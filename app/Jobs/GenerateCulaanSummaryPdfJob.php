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
        $folderPath = "pdfs/culaan/{$this->culaanId}";


        $statuses = [
            'D' => 'BN',
            'A' => 'PH',
            'C' => 'PAS',
            'E' => 'TP',
            'O' => 'BC',
        ];

        // -------------------------
        // Fetch Culaan and related election info
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
        // Count filtered pengundi
        // -------------------------
        $query = DB::table('culaan_pengundis')->where('culaan_id', $this->culaanId);

        if (!empty($this->filters['lokaliti'])) {
            $query->where('lokaliti', 'like', "%{$this->filters['lokaliti']}%");
        }

        if (!empty($this->filters['status_culaan'])) {
            $query->where('status_culaan', 'like', "{$this->filters['status_culaan']}%");
        }

        if (!empty($this->filters['search_name'])) {
            $search = $this->filters['search_name'];
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('no_kp', 'like', "%{$search}%");
            });
        }

        $totalFilteredPengundi = $query->count();

        // -------------------------
        // Generate Summary PDF
        // -------------------------
        $pdf = Pdf::loadView('culaan.culaan_summary_pdf', [
            'culaan' => $culaan,
            'filters' => $this->filters,
            'totalPengundi' => $totalFilteredPengundi,
        ])->setPaper('a4', 'portrait');

        // -------------------------
        // Build filename
        // -------------------------
        $lokaliti = !empty($this->filters['lokaliti'])
            ? preg_replace('/[^A-Za-z0-9]/', '_', $this->filters['lokaliti'])
            : 'all';

        $status = !empty($this->filters['status_culaan'])
            ? preg_replace('/[^A-Za-z0-9]/', '_', $this->filters['status_culaan'])
            : 'all';

        $search = !empty($this->filters['search_name'])
            ? preg_replace('/[^A-Za-z0-9]/', '_', $this->filters['search_name'])
            : 'all';

        $fileName = "temp_culaan_{$this->culaanId}_lokaliti_{$lokaliti}_status_{$statuses[$status]}_search_{$search}_summary.pdf";

        $summaryPath = "{$folderPath}/{$fileName}";

        Storage::disk('public')->put($summaryPath, $pdf->output());

        Log::info("Summary PDF generated: {$summaryPath}");
    }
}