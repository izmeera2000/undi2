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

            if (!$type || !$series) {
                Log::error('Summary Job missing type/series', $this->filters);
                return;
            }

            if (!isset($this->PRMAP[$type][$series])) {
                Log::error('PRMAP not found for type/series', [
                    'type' => $type,
                    'series' => $series
                ]);
                return;
            }

            $selectedPRUYear = $this->PRMAP[$type][$series];
            $selectedPRUDate = $selectedPRUYear . '-12-31';

            Log::info('PRU Year determined', [
                'year' => $selectedPRUYear,
                'date' => $selectedPRUDate
            ]);

            // ==============================
            // QUERY
            // ==============================
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

            Log::info('Summary Query Completed', [
                'row_count' => $rows->count()
            ]);

            if ($rows->isEmpty()) {
                Log::warning('Summary Job returned empty result');
                return;
            }

            // ==============================
            // PDF GENERATION
            // ==============================
            Log::info('Generating Summary PDF...');

            $pdf = Pdf::loadView('pengundi.pdf.list_data_pdf', [
                'data' => $rows,
                'filters' => $this->filters
            ])->setPaper('a4', 'landscape');

            $folderPath = "pdfs/{$type}/{$series}/{$this->filters['dm']}";
            $fileName = "{$this->filters['dm']}_summary.pdf";
            $fullPath = "{$folderPath}/{$fileName}";

            Storage::disk('public')->put($fullPath, $pdf->output());

            Log::info('Summary PDF Saved', [
                'path' => $fullPath
            ]);

            // ==============================
            // NOTIFICATION
            // ==============================
            $user = User::find($this->userId);

            if (!$user) {
                Log::error('User not found for notification', [
                    'userId' => $this->userId
                ]);
                return;
            }

            $user->notify(new LokalitiPdfGenerated($fullPath));

            Log::info('Notification sent successfully', [
                'userId' => $this->userId
            ]);

            unset($pdf);
            unset($rows);
            gc_collect_cycles();

            Log::info('Summary Job COMPLETED SUCCESSFULLY');
        } catch (Throwable $e) {

            Log::error('Summary Job FAILED', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            throw $e; // important so batch knows it failed
        }
    }
}