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
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use App\Notifications\LokalitiPdfGenerated;
use Throwable;

class GenerateLokalitiSummaryPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected array $filters;
    protected array $PRMAP;
    protected int $userId;

    public function __construct(array $filters, array $PRMAP, int $userId)
    {
        $this->filters = $filters;
        $this->PRMAP = $PRMAP;
        $this->userId = $userId;
    }

    public function handle()
    {
        Log::info('Summary Job STARTED', [
            'filters' => $this->filters,
            'userId' => $this->userId
        ]);

        try {

            $type = $this->filters['type'] ?? null;
            $series = $this->filters['series'] ?? null;
            $dun = $this->filters['dun'] ?? null;
            $dm = $this->filters['dm'] ?? null;
            $parlimen = $this->filters['parlimen'] ?? null;

            if (!$type || !$series || !isset($this->PRMAP[$type][$series])) {
                Log::error('Invalid type/series', $this->filters);
                return;
            }

            $selectedPRUYear = $this->PRMAP[$type][$series];
            $selectedPRUDate = $selectedPRUYear . '-12-31';

            /*
            |--------------------------------------------------------------------------
            | STEP 1: PURE SQL AGGREGATION (NO PHP GROUPING)
            |--------------------------------------------------------------------------
            */
            $rows = DB::table(function ($query) use ($type, $series, $parlimen, $dun, $dm, $selectedPRUDate) {
                $query->from('pengundi as p')
                    ->select('p.id', 'p.kod_lokaliti', 'p.saluran', 'l.nama_lokaliti')
                    ->distinct()
                    ->join('lokaliti as l', function ($join) use ($selectedPRUDate) {
                        $join->on('p.kod_lokaliti', '=', 'l.kod_lokaliti')
                            ->where('l.effective_from', '<=', $selectedPRUDate)
                            ->where(function ($q) use ($selectedPRUDate) {
                                $q->whereNull('l.effective_to')
                                    ->orWhere('l.effective_to', '>=', $selectedPRUDate);
                            });
                    })
                    ->join('dm as d', function ($join) use ($selectedPRUDate) {
                        $join->on('l.koddm', '=', 'd.koddm')
                            ->where('d.effective_from', '<=', $selectedPRUDate)
                            ->where(function ($q) use ($selectedPRUDate) {
                                $q->whereNull('d.effective_to')
                                    ->orWhere('d.effective_to', '>=', $selectedPRUDate);
                            });
                    })
                    ->join('dun as dn', function ($join) use ($selectedPRUDate) {
                        $join->on('d.kod_dun', '=', 'dn.kod_dun')
                            ->where('dn.effective_from', '<=', $selectedPRUDate)
                            ->where(function ($q) use ($selectedPRUDate) {
                                $q->whereNull('dn.effective_to')
                                    ->orWhere('dn.effective_to', '>=', $selectedPRUDate);
                            });
                    })
                    ->where('p.pilihan_raya_type', $type)
                    ->where('p.pilihan_raya_series', $series)
                    ->where('dn.parlimen_id', $parlimen)
                    ->where('d.kod_dun', $dun)
                    ->where('l.koddm', $dm);
            }, 'p') // alias the subquery as p
                ->selectRaw("
    p.kod_lokaliti,
    p.nama_lokaliti,
    SUM(CASE WHEN p.saluran = 1 THEN 1 ELSE 0 END) AS saluran_1,
    SUM(CASE WHEN p.saluran = 2 THEN 1 ELSE 0 END) AS saluran_2,
    SUM(CASE WHEN p.saluran = 3 THEN 1 ELSE 0 END) AS saluran_3,
    SUM(CASE WHEN p.saluran = 4 THEN 1 ELSE 0 END) AS saluran_4,
    SUM(CASE WHEN p.saluran = 5 THEN 1 ELSE 0 END) AS saluran_5,
    SUM(CASE WHEN p.saluran = 6 THEN 1 ELSE 0 END) AS saluran_6,
    SUM(CASE WHEN p.saluran = 7 THEN 1 ELSE 0 END) AS saluran_7,
    COUNT(*) AS total
")
                ->groupBy('p.kod_lokaliti', 'p.nama_lokaliti')
                ->orderBy('p.kod_lokaliti')
                ->get();


                
            if ($rows->isEmpty()) {
                Log::warning('No pengundi found for summary');
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 2: Generate PDF (Directly use $rows)
            |--------------------------------------------------------------------------
            */

            $pdf = Pdf::loadView('pengundi.pdf.list_data_pdf', [
                'data' => $rows,
                'filters' => $this->filters
            ])->setPaper('a4', 'landscape');

            $folderPath = "pdfs/{$type}/{$series}/{$this->filters['dm']}";
            $fileName = "{$this->filters['dm']}_summary.pdf";
            $fullPath = "{$folderPath}/{$fileName}";

            Storage::disk('public')->put($fullPath, $pdf->output());

            Log::info('Summary PDF saved', ['path' => $fullPath]);

            /*
            |--------------------------------------------------------------------------
            | STEP 3: Notify User
            |--------------------------------------------------------------------------
            */

            $user = User::find($this->userId);
            if ($user) {
                $user->notify(new LokalitiPdfGenerated($fullPath));
            }

            Log::info('Summary Job COMPLETED SUCCESSFULLY');

        } catch (Throwable $e) {

            Log::error('Summary Job FAILED', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            throw $e;
        }
    }

}