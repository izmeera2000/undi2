<?php

namespace App\Jobs;


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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filters;
    protected $PRMAP;
    protected $kod_lokaliti;

    public function __construct($filters, $PRMAP, $kod_lokaliti)
    {
        $this->filters = $filters;
        $this->PRMAP = $PRMAP;
        $this->kod_lokaliti = $kod_lokaliti;
    }

    public function handle()
    {
        $type = $this->filters['type'];
        $series = $this->filters['series'];

        if (!isset($this->PRMAP[$type][$series])) {
            return;
        }

        $records = DB::table('pengundi')
            ->join('lokaliti', 'pengundi.kod_lokaliti', '=', 'lokaliti.kod_lokaliti')
            ->where('pengundi.pilihan_raya_type', $type)
            ->where('pengundi.pilihan_raya_series', $series)
            ->where('pengundi.kod_lokaliti', $this->kod_lokaliti)
            ->select(
                'pengundi.nama',
                'pengundi.saluran',
                'pengundi.nokp_baru',
                'pengundi.bangsa',
                'pengundi.jantina',
                'pengundi.alamat_spr',
                'lokaliti.nama_lokaliti'
            )
             ->get();

        if ($records->isEmpty()) {
            return;
        }

        $lokalitiData = [
            'kod_lokaliti' => $this->kod_lokaliti,
            'nama_lokaliti' => $records->first()->nama_lokaliti ?? null,
            'pilihan_raya_type' => $type,
            'pilihan_raya_series' => $series,
            'details' => $records->toArray()
        ];

        $pdf = Pdf::loadView('pengundi.pdf.list_data_pdf_single', [
            'data' => [$lokalitiData],
            'filters' => $this->filters
        ])->setPaper('a4', 'portrait');

        Storage::put(
            "public/pdfs/{$this->kod_lokaliti}.pdf",
            $pdf->output()
        );
    }
}