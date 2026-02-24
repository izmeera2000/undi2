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

class GeneratePengundiPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filters;
    protected $PRMAP;

    public function __construct($filters, $PRMAP)
    {
        $this->filters = $filters;
        $this->PRMAP = $PRMAP;
    }

    public function handle()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $type = $this->filters['type'];
        $series = $this->filters['series'];

        if (!isset($this->PRMAP[$type][$series])) {
            return;
        }

        $selectedPRUYear = $this->PRMAP[$type][$series];
        $selectedPRUDate = $selectedPRUYear . '-12-31';

        // ==========================================
        // STEP 1: Get distinct lokaliti first
        // ==========================================
        $lokalitiList = DB::table('pengundi')
            ->join('lokaliti', function ($join) use ($selectedPRUDate) {
                $join->on('pengundi.kod_lokaliti', '=', 'lokaliti.kod_lokaliti')
                    ->where('lokaliti.effective_from', '<=', $selectedPRUDate)
                    ->where(function ($q) use ($selectedPRUDate) {
                        $q->whereNull('lokaliti.effective_to')
                            ->orWhere('lokaliti.effective_to', '>=', $selectedPRUDate);
                    });
            })
            ->join('dm', 'lokaliti.koddm', '=', 'dm.koddm')
            ->join('dun', 'dm.kod_dun', '=', 'dun.kod_dun')
            ->where('pengundi.pilihan_raya_type', $type)
            ->where('pengundi.pilihan_raya_series', $series)
            ->where('dun.parlimen_id', $this->filters['parlimen'])
            ->where('dm.kod_dun', $this->filters['dun'])
            ->where('lokaliti.koddm', $this->filters['dm'])
            ->distinct()
            ->pluck('pengundi.kod_lokaliti');

        if ($lokalitiList->isEmpty()) {
            return;
        }

        // ==========================================
        // STEP 2: Process one lokaliti at a time
        // ==========================================
        foreach ($lokalitiList as $kod_lokaliti) {

            $records = DB::table('pengundi')
                ->join('lokaliti', 'pengundi.kod_lokaliti', '=', 'lokaliti.kod_lokaliti')
                ->where('pengundi.pilihan_raya_type', $type)
                ->where('pengundi.pilihan_raya_series', $series)
                ->where('pengundi.kod_lokaliti', $kod_lokaliti)
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
                continue;
            }

            $lokalitiData = [
                'kod_lokaliti' => $kod_lokaliti,
                'nama_lokaliti' => $records->first()->nama_lokaliti ?? null,
                'pilihan_raya_type' => $type,
                'pilihan_raya_series' => $series,
                'details' => $records->map(function ($p) {
                    return [
                        'nama' => $p->nama,
                        'saluran' => $p->saluran,
                        'nokp_baru' => $p->nokp_baru,
                        'bangsa' => $p->bangsa,
                        'jantina' => $p->jantina,
                        'alamat_spr' => $p->alamat_spr,
                    ];
                })->toArray()
            ];

            $pdf = Pdf::loadView('pengundi.pdf.list_data_pdf_single', [
                'data' => [$lokalitiData],
                'filters' => $this->filters
            ])->setPaper('a4', 'portrait');

            $fileName = "{$kod_lokaliti}.pdf";
            $filePath = "public/pdfs/{$fileName}";

            Storage::put($filePath, $pdf->output());

            // FREE MEMORY (VERY IMPORTANT)
            unset($pdf);
            unset($records);
            unset($lokalitiData);
            gc_collect_cycles();
        }
    }
}