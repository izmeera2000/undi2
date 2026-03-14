<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Batchable;

class GenerateSingleCulaanPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected int $culaanId;
    protected array $filters;
    protected int $page;
    protected int $perPage;

    public function __construct(int $culaanId, array $filters, int $page = 1, int $perPage = 200)
    {
        $this->culaanId = $culaanId;
        $this->filters = $filters;
        $this->page = $page;
        $this->perPage = $perPage;
    }

    public function handle()
    {
        $query = DB::table('culaan_pengundis')
            ->select([
                'id',
                'nama',
                'no_kp',
                'jantina',
                'bangsa',
                'pm',
                'lokaliti',
                'status_culaan'
            ])
            ->where('culaan_id', $this->culaanId);

        if (!empty($this->filters['lokaliti'])) {
            $query->where('lokaliti', 'like', "%{$this->filters['lokaliti']}%");
        }

        if (!empty($this->filters['status_culaan'])) {
            $query->where('status_culaan', 'like', $this->filters['status_culaan'] . '%');
        }

        if (!empty($this->filters['search_name'])) {
            $search = $this->filters['search_name'];

            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_kp', 'like', "%{$search}%");
            });
        }

        $rows = $query
            ->orderBy('id')
            ->forPage($this->page, $this->perPage)
            ->get();

        $pengundi = [];

        $statuses = [
            'D' => 'BN',
            'A' => 'PH',
            'C' => 'PAS',
            'E' => 'TP',
            'O' => 'BC',
            'all' => 'all',
        ];

        foreach ($rows as $row) {

            $statusCode = $row->status_culaan
                ? strtoupper(substr(trim($row->status_culaan), 0, 1))
                : 'O';

            $pengundi[] = [
                'id' => $row->id,
                'nama' => $row->nama,
                'no_kp' => $row->no_kp,
                'lokaliti_details' => $row->pm,
                'status_culaan' => $statuses[$statusCode] ?? $statusCode,
            ];
        }

        if (empty($pengundi)) {
            Log::warning("No data for Culaan {$this->culaanId} page {$this->page}");
            return;
        }

        $pdf = Pdf::loadView('culaan.culaan_pdf', [
            'culaan' => DB::table('culaans')->find($this->culaanId),
            'pengundi' => $pengundi,
        ])->setPaper('a4', 'portrait');

        // -------------------------
        // Build filename from filters
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

        $fileName = "temp_culaan_{$this->culaanId}_lokaliti_{$lokaliti}_status_{$statuses[$status]}_search_{$search}_page{$this->page}.pdf";

        $filePath = "pdfs/culaan/{$this->culaanId}/{$fileName}";

        Storage::disk('public')->put($filePath, $pdf->output());

        Log::info("Generated PDF: {$filePath}");
    }
}