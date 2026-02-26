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

class GenerateSingleLokalitiPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $filters;
    protected $PRMAP;
    protected $page;
    protected $perPage;
    protected $kod_lokaliti;

    public function __construct($filters, $prmap, $kod_lokaliti, $page = 1, $perPage = 200)
    {
        $this->filters = $filters;
        $this->PRMAP = $prmap;
        $this->kod_lokaliti = $kod_lokaliti;
        $this->page = $page;
        $this->perPage = $perPage;
    }

    public function handle()
    {
        $type = $this->filters['type'];
        $series = $this->filters['series'];

        if (!isset($this->PRMAP[$type][$series])) {
            return;
        }

        $selectedPRUYear = $this->PRMAP[$type][$series];
        $selectedPRUDate = $selectedPRUYear . '-12-31';

        // -----------------------------
        // Fetch pengundi with valid lokaliti date range
        // -----------------------------
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

        if ($records->isEmpty())
            return;

        $data = [
            'kod_lokaliti' => $this->kod_lokaliti,
            'nama_lokaliti' => $records->first()->nama_lokaliti ?? null,
            'pilihan_raya_type' => $type,
            'pilihan_raya_series' => $series,
            'details' => $records->toArray(),
            

        ];
        $startNumber = ($this->page - 1) * $this->perPage + 1;


        $pdf = Pdf::loadView('pengundi.pdf.list_data_pdf_single', [
            'data' => [$data],
            'filters' => $this->filters,
            'page' => $this->page,
             'startNumber' => $startNumber,
        ])->setPaper('a4', 'portrait');


        $folderPath = "pdfs/{$this->filters['type']}/{$this->filters['series']}/{$this->filters['dm']}";

        $fileName = "{$this->kod_lokaliti}_page_{$this->page}.pdf";

        Storage::disk('public')->put(
            "{$folderPath}/{$fileName}",
            $pdf->output()
        );
    }
}