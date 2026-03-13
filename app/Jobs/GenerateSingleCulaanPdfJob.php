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
    protected string $kodLokaliti;
    protected int $page;
    protected int $perPage;

    public function __construct(int $culaanId, array $filters, string $kodLokaliti, int $page = 1, int $perPage = 200)
    {
        $this->culaanId = $culaanId;
        $this->filters = $filters;
        $this->kodLokaliti = $kodLokaliti;
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
                'kod_lokaliti',
                'status_culaan'
            ])
            ->where('culaan_id', $this->culaanId)
            ->where('kod_lokaliti', $this->kodLokaliti);

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

        foreach ($rows as $row) {

            $jantinaAll = [
                'L' => 'Lelaki',
                'P' => 'Perempuan',
            ];

            $bangsaAll = [
                'M' => 'Melayu',
                'C' => 'Cina',
                'I' => 'India',
                'L' => 'Lain-lain',
            ];

            $statuses = [
                'D' => 'BN',
                'A' => 'PH',
                'C' => 'PAS',
                'E' => 'TP',
                'O' => 'BC',
            ];

            $statusCode = $row->status_culaan
                ? strtoupper(substr(trim($row->status_culaan), 0, 1))
                : 'O';

            $pengundi[] = [
                'id' => $row->id,
                'nama' => $row->nama,
                'no_kp' => $row->no_kp,
                 'lokaliti_details' => $row->pm ,
                'status_culaan' => $statuses[$statusCode] ?? $statusCode,
            ];
        }

        if (empty($pengundi)) {
            Log::warning("No data for Culaan {$this->culaanId} lokaliti {$this->kodLokaliti} page {$this->page}");
            return;
        }
        $pdf = Pdf::loadView('culaan.culaan_pdf', [
            'culaan' => DB::table('culaans')->find($this->culaanId),
            'pengundi' => $pengundi,
        ])->setPaper('a4', 'portrait');

        $filePath = "pdfs/culaan/{$this->culaanId}/temp_{$this->kodLokaliti}_page{$this->page}.pdf";

        Storage::disk('public')->put($filePath, $pdf->output());

        Log::info("Generated PDF: {$filePath}");
    }
}