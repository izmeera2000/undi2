<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GenerateCulaanSummaryPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $culaanId;
    protected array $filters;
    protected int $userId;

    public function __construct(int $culaanId, array $filters, int $userId)
    {
        $this->culaanId = $culaanId;
        $this->filters = $filters;
        $this->userId = $userId;
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
        $totalFilteredPengundi = DB::table('culaan_pengundis')
            ->where('culaan_id', $this->culaanId)

            ->when(!empty($this->filters['lokaliti']), function ($q) {
                $q->where('kod_lokaliti', 'like', '%' . $this->filters['lokaliti'] . '%');
            })

            ->when(!empty($this->filters['status_culaan']), function ($q) {
                $q->where('status_culaan', $this->filters['status_culaan']);
            })

            ->when(!empty($this->filters['search_name']), function ($q) {
                $search = $this->filters['search_name'];

                $q->where(function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%")
                        ->orWhere('no_kp', 'like', "%{$search}%");
                });
            })

            ->count();

        Log::info('Count query completed', [
            'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
        ]);

        $logo = base64_encode(file_get_contents(public_path('assets/img/UMNO_logo.png')));

        // -------------------------
        // Generate PDF
        // -------------------------
        $pdf = Pdf::loadView('culaan.culaan_summary_pdf', [
            'culaan' => $culaan,
            'filters' => $this->filters,
            'totalPengundi' => $totalFilteredPengundi,
            'logo' => $logo,

            'generatedAt' => now('Asia/Kuala_Lumpur')->format('d M Y H:i'),
        ])->setPaper('a4', 'portrait');

        Log::info('PDF rendered', [
            'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
        ]);

        // -------------------------
        // Filename
        // -------------------------
        $sanitize = fn($value) =>
            $value ? preg_replace('/[^A-Za-z0-9]/', '_', $value) : 'all';

        $lokaliti = $sanitize($this->filters['lokaliti'] ?? null);
        $search = $sanitize($this->filters['search_name'] ?? null);

        $statusLabel = $statuses[$this->filters['status_culaan'] ?? ''] ?? 'all';

        $fileName = "temp_culaan_{$this->culaanId}_lokaliti_{$lokaliti}_status_{$statusLabel}_search_{$search}_summary.pdf";

        $summaryPath = "{$folderPath}/{$fileName}";

        Storage::disk('public')->makeDirectory($folderPath);

        Storage::disk('public')->put($summaryPath, $pdf->output());

        $endMemory = memory_get_usage(true);
        $peakMemory = memory_get_peak_usage(true);

        unset($pdf);
        gc_collect_cycles();

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