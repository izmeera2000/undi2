<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;
use Log;

class GenerateSingleLokalitiPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected array $filters;
    protected string $kod_lokaliti;
    protected int $page;
    protected int $perPage;

    public $tries = 5;
    public $timeout = 300;

    public function __construct(array $filters, string $kod_lokaliti, int $page = 1, int $perPage = 200)
    {
        $this->filters = $filters;
        $this->kod_lokaliti = $kod_lokaliti;
        $this->page = $page;
        $this->perPage = $perPage;
    }

    public function handle()
    {
        try {

            $type = $this->filters['type'] ?? null;
            $series = isset($this->filters['series']) ? (int) $this->filters['series'] : null;

            if (!$type || !$series) {
                Log::error('Missing type/series in GenerateSingleLokalitiPdfJob', $this->filters);
                return;
            }

            // --------------------------------
            // Fetch election year from DB
            // --------------------------------
            $selectedPRUYear = DB::table('elections')
                ->where('type', $type)
                ->where('number', $series)
                ->value('year');

            if (!$selectedPRUYear) {
                Log::error('Election not found in DB', [
                    'type' => $type,
                    'series' => $series
                ]);
                return;
            }

            $selectedPRUDate = $selectedPRUYear . '-12-31';

            // --------------------------------
            // Fetch pengundi
            // --------------------------------
            $records = DB::table('pengundi')
                ->join('lokaliti', function ($join) use ($selectedPRUDate) {
                    $join->on('pengundi.kod_lokaliti', '=', 'lokaliti.kod_lokaliti')
                        ->where('lokaliti.effective_from', '<=', $selectedPRUDate)
                        ->where(function ($q) use ($selectedPRUDate) {
                            $q->whereNull('lokaliti.effective_to')
                                ->orWhere('lokaliti.effective_to', '>=', $selectedPRUDate);
                        });
                })
                ->where('pengundi.pilihan_raya_type', $type)
                ->where('pengundi.pilihan_raya_series', $series)
                ->where('pengundi.kod_lokaliti', $this->kod_lokaliti)
                ->orderBy('pengundi.id')
                ->forPage($this->page, $this->perPage)
                ->select(
                    'pengundi.nama',
                    'pengundi.saluran',
                    'pengundi.nokp_baru',
                    'pengundi.bangsa',
                    'pengundi.jantina',
                    'pengundi.alamat_spr',
                    'pengundi.kod_lokaliti',
                    'lokaliti.nama_lokaliti'
                )
                ->get();

            if ($records->isEmpty()) {
                return;
            }

            $startNumber = ($this->page - 1) * $this->perPage + 1;

            $data = [
                'kod_lokaliti' => $this->kod_lokaliti,
                'nama_lokaliti' => $records->first()->nama_lokaliti ?? null,
                'pilihan_raya_type' => $type,
                'pilihan_raya_series' => $series,
                'details' => $records->toArray(),
            ];

            $pdf = Pdf::loadView('pengundi.pdf.list_data_pdf_single', [
                'data' => [$data],
                'filters' => $this->filters,
                'page' => $this->page,
                'startNumber' => $startNumber,
            ])->setPaper('a4', 'portrait');

            $folderPath = "pdfs/{$type}/{$series}/{$this->filters['dm']}";
            $fileName = "{$this->kod_lokaliti}_page_{$this->page}.pdf";
            $fullPath = "{$folderPath}/{$fileName}";

            // ✅ Fix: check correct full path
            if (Storage::disk('public')->exists($fullPath)) {
                Log::info("PDF already exists, skipping", ['file' => $fullPath]);
                return;
            }

            Storage::disk('public')->put($fullPath, $pdf->output());

            unset($pdf, $records);
            gc_collect_cycles();

        } catch (Throwable $e) {

            Log::error('GenerateSingleLokalitiPdfJob failed', [
                'lokaliti' => $this->kod_lokaliti,
                'page' => $this->page,
                'error' => $e->getMessage(),
            ]);

            throw $e; // allow retry
        }
    }

    public function failed(Throwable $exception)
    {
        Log::error('GenerateSingleLokalitiPdfJob permanently failed', [
            'lokaliti' => $this->kod_lokaliti,
            'page' => $this->page,
            'error' => $exception->getMessage(),
        ]);
    }
}