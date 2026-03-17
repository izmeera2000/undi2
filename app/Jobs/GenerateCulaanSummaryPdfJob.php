<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Mpdf\Mpdf;

class GenerateCulaanSummaryPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $culaanId;
    protected array $filters;
    protected int $userId;
    protected array $toc;

    public function __construct(int $culaanId, array $filters, int $userId, array $toc)
    {
        $this->culaanId = $culaanId;
        $this->filters = $filters;
        $this->userId = $userId;
        $this->toc = $toc;
    }

    public function handle()
    {
        ini_set('memory_limit', '256M');
        $startMemory = memory_get_usage(true);

        Log::info('CulaanSummaryPdfJob started', [
            'culaan_id' => $this->culaanId,
            'memory_start_mb' => round($startMemory / 1024 / 1024, 2)
        ]);

        $folderPath = "pdfs/culaan/{$this->culaanId}";

        $statuses = [
            'D' => 'BN',
            'A' => 'PH',
            'C' => 'PAS',
            'E' => 'TP',
            'O' => 'BC',
        ];

        // -------------------------
        // Fetch Culaan + Election
        // -------------------------
        $culaan = DB::table('culaans')
            ->leftJoin('elections', 'culaans.election_id', '=', 'elections.id')
            ->select(
                'culaans.*',
                'elections.type as election_type',
                'elections.number as election_number',
                'elections.year as election_year'
            )
            ->where('culaans.id', $this->culaanId)
            ->first();

        if (!$culaan) {
            Log::warning("Culaan not found: {$this->culaanId}");
            return;
        }

        // -------------------------
        // Count query
        // -------------------------
        $query = DB::table('culaan_pengundis')
            ->where('culaan_id', $this->culaanId)
            ->when(!empty($this->filters['lokaliti']), function ($q) {
                $q->where('kod_lokaliti', $this->filters['lokaliti']);
            })
            ->when(!empty($this->filters['status_culaan']), function ($q) {
                $q->where('status_culaan', $this->filters['status_culaan']);
            })
            ->when(!empty($this->filters['search_name']), function ($q) {
                $search = trim($this->filters['search_name']);

                $q->where(function ($qq) use ($search) {
                    if (str_starts_with($search, '*') && str_ends_with($search, '*')) {
                        $pattern = "%" . substr($search, 1, -1) . "%";
                    } elseif (str_starts_with($search, '*')) {
                        $pattern = "%" . substr($search, 1);
                    } elseif (str_ends_with($search, '*')) {
                        $pattern = substr($search, 0, -1) . "%";
                    } else {
                        $pattern = "%{$search}%";
                    }

                    $qq->where('nama', 'like', $pattern)
                        ->orWhere('no_kp', 'like', $pattern);
                });
            });

        $totalFilteredPengundi = $query->count();

        Log::info('Count query completed', [
            'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
        ]);

        $logo = base64_encode(file_get_contents(public_path('assets/img/UMNO_logo.png')));

        // -------------------------
        // Render HTML
        // -------------------------
        $html = view('culaan.culaan_summary_pdf', [
            'culaan' => $culaan,
            'filters' => $this->filters,
            'totalPengundi' => $totalFilteredPengundi,
            'logo' => $logo,
            'culaan_date' => Carbon::parse($culaan->date)
                                ->timezone('Asia/Kuala_Lumpur')
                                ->format('d M Y H:i'),
            'generatedAt' => now('Asia/Kuala_Lumpur')->format('d M Y H:i'),
            'toc' => $this->toc,
        ])->render();

        // -------------------------
        // Generate PDF using mPDF
        // -------------------------
        $mpdf = new Mpdf([
            'format' => 'A4',
            'orientation' => 'P',
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_left' => 10,
            'margin_right' => 10
        ]);

        // Performance tweaks
        $mpdf->simpleTables = true;
        $mpdf->packTableData = true;
 
        $mpdf->WriteHTML($html);

        // -------------------------
        // Save PDF
        // -------------------------
        $sanitize = fn($value) =>
            $value ? preg_replace('/[^A-Za-z0-9]/', '_', $value) : 'all';

        $lokaliti = $sanitize($this->filters['lokaliti'] ?? null);
        $search = $sanitize($this->filters['search_name'] ?? null);
        $statusLabel = $statuses[$this->filters['status_culaan'] ?? ''] ?? 'all';

        $fileName = "temp_culaan_{$this->culaanId}_lokaliti_{$lokaliti}_status_{$statusLabel}_search_{$search}_summary.pdf";

        $summaryPath = "{$folderPath}/{$fileName}";

        Storage::disk('public')->makeDirectory($folderPath);
        Storage::disk('public')->put($summaryPath, $mpdf->Output('', 'S'));

        unset($mpdf);
        gc_collect_cycles();

        $endMemory = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);

        Log::info('Summary PDF generated', [
            'culaan_id' => $this->culaanId,
            'path' => $summaryPath,
            'user_id' => $this->userId,
            'memory_end_mb' => round($endMemory / 1024 / 1024, 2),
            'memory_peak_mb' => round($peakMemory / 1024 / 1024, 2),
            'memory_used_mb' => round(($endMemory - $startMemory) / 1024 / 1024, 2)
        ]);
    }
}