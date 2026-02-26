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

class GenerateLokalitiSummaryPdfJob implements ShouldQueue
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
        // STEP 1: SQL Aggregation (NO Collection groupBy)
        // ==========================================
        $rows = DB::table('pengundi')
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
            ->join('parlimen', 'dun.parlimen_id', '=', 'parlimen.id')
            ->where('pengundi.pilihan_raya_type', $type)
            ->where('pengundi.pilihan_raya_series', $series)
            ->where('dun.parlimen_id', $this->filters['parlimen'])
            ->where('dm.kod_dun', $this->filters['dun'])
            ->where('lokaliti.koddm', $this->filters['dm'])
            ->selectRaw("
                pengundi.kod_lokaliti,
                lokaliti.nama_lokaliti,
                SUM(CASE WHEN pengundi.saluran = 1 THEN 1 ELSE 0 END) as saluran_1,
                SUM(CASE WHEN pengundi.saluran = 2 THEN 1 ELSE 0 END) as saluran_2,
                SUM(CASE WHEN pengundi.saluran = 3 THEN 1 ELSE 0 END) as saluran_3,
                SUM(CASE WHEN pengundi.saluran = 4 THEN 1 ELSE 0 END) as saluran_4,
                SUM(CASE WHEN pengundi.saluran = 5 THEN 1 ELSE 0 END) as saluran_5,
                SUM(CASE WHEN pengundi.saluran = 6 THEN 1 ELSE 0 END) as saluran_6,
                SUM(CASE WHEN pengundi.saluran = 7 THEN 1 ELSE 0 END) as saluran_7,
                COUNT(*) as total
            ")
            ->groupBy('pengundi.kod_lokaliti', 'lokaliti.nama_lokaliti')
            ->orderBy('pengundi.kod_lokaliti')
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        // ==========================================
        // STEP 2: Generate PDF
        // ==========================================
        $pdf = Pdf::loadView('pengundi.pdf.list_data_pdf_summary', [
            'data' => $rows,
            'filters' => $this->filters
        ])->setPaper('a4', 'landscape');


        $folderPath = "pdfs/{$type}/{$series}/{$this->filters['dm']}";

        $fileName = "summary_{$type}_{$series}.pdf";

        Storage::disk('public')->put(
            "{$folderPath}/{$fileName}",
            $pdf->output()
        );

        unset($pdf);
        unset($rows);
        gc_collect_cycles();
    }
}