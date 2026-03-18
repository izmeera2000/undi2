<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateLokalitiBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $filters;
    protected int $userId;

    public function __construct(array $filters, int $userId)
    {
        $this->filters = $filters;
        $this->userId = $userId;
    }

    public function handle()
    {
        $startTime = microtime(true);

        Log::info('===== GenerateLokalitiBatchJob START =====', [
            'filters' => $this->filters,
            'userId' => $this->userId,
            'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
        ]);

        $type = $this->filters['type'] ?? null;
        $series = isset($this->filters['series']) ? (int) $this->filters['series'] : null;
        $dun = $this->filters['dun'] ?? null;
        $dm = $this->filters['dm'] ?? null;

        if (!$type || !$series || !$dun || !$dm) {
            Log::error('Missing required filters.', $this->filters);
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Get election year
        |--------------------------------------------------------------------------
        */
        $selectedPRUYear = DB::table('elections')
            ->where('type', $type)
            ->where('number', $series)
            ->value('year');

        if (!$selectedPRUYear) {
            Log::error('Election not found.', [
                'type' => $type,
                'series' => $series
            ]);
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Prepare PDF folder
        |--------------------------------------------------------------------------
        */

        $folderPath = "pdfs/{$type}/{$series}/{$dm}";

        if (!Storage::disk('public')->exists($folderPath)) {
            Storage::disk('public')->makeDirectory($folderPath);
        } else {
            $files = Storage::disk('public')->files($folderPath);
            if (!empty($files)) {
                Storage::disk('public')->delete($files);
                Log::info("Deleted existing PDFs", [
                    'deleted_count' => count($files)
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Get valid DM
        |--------------------------------------------------------------------------
        */

        $validDMs = DB::table('dm')
            ->whereYear('effective_from', '<=', $selectedPRUYear)
            ->where(function ($q) use ($selectedPRUYear) {
                $q->whereYear('effective_to', '>=', $selectedPRUYear)
                    ->orWhereNull('effective_to');
            })
            ->where('kod_dun', $dun)
            ->where('kod_dm', $dm)
            ->pluck('kod_dm')
            ->unique()
            ->toArray();

        if (empty($validDMs)) {
            Log::warning('No valid DM found.');
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Get Lokaliti
        |--------------------------------------------------------------------------
        */
        // 1. Fetching the list (Key = Name, Value = Code)
        $lokalitiList = DB::table('lokaliti')
            ->whereIn('kod_dm', $validDMs)
            ->pluck('kod_lokaliti', 'nama_lokaliti')
            ->unique()
            ->toArray();

        if (empty($lokalitiList)) {
            Log::warning('No lokaliti found.');
            return;
        }

        $perPage = 22;
        $jobs = [];

        // 2. Change the foreach to capture both Key and Value
        foreach ($lokalitiList as $namaLokaliti => $kodLokaliti) {

            // Fetch records for this specific code
            $records = DB::table('pengundi')
                ->where('pilihan_raya_type', $type)
                ->where('pilihan_raya_series', $series)
                ->where('kod_lokaliti', $kodLokaliti)
                ->orderBy('id')
                ->get();

            if ($records->isEmpty()) {
                continue;
            }

            // 3. Pass both $kodLokaliti and $namaLokaliti to the Job
            $jobs[] = new GenerateSingleLokalitiPdfJobOptimized(
                $this->filters,
                $kodLokaliti,
                $namaLokaliti, // Added this parameter
                $records->toArray(),
                $perPage
            );
        }

        if (empty($jobs)) {
            Log::warning('No PDF jobs created.');
            return;
        }

        Log::info('PDF jobs prepared', [
            'total_jobs' => count($jobs)
        ]);

        $filtersCopy = $this->filters;
        $userIdCopy = $this->userId;
        $typeCopy = $type;
        $seriesCopy = $series;
        $dmCopy = $dm;
        $lokalitiCopy = $lokalitiList;

        /*
        |--------------------------------------------------------------------------
        | Batch: Generate pages
        |--------------------------------------------------------------------------
        */

        Bus::batch($jobs)
            ->then(function (Batch $batch) use ($lokalitiCopy, $typeCopy, $seriesCopy, $dmCopy, $filtersCopy, $userIdCopy) {

                Log::info('Page batch finished. Starting merge jobs.');

                $mergeJobs = [];

                foreach ($lokalitiCopy as $kodLokaliti) {

                    $mergeJobs[] = new MergeLokalitiPdfJob(
                        $kodLokaliti,
                        [
                            'type' => $typeCopy,
                            'series' => $seriesCopy,
                            'dm' => $dmCopy,
                        ]
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Batch: Merge PDFs
                |--------------------------------------------------------------------------
                */

                Bus::batch($mergeJobs)
                    ->then(function () use ($filtersCopy, $userIdCopy) {

                        Log::info('Merge batch finished. Generating summary.');

                        GenerateLokalitiSummaryPdfJob::dispatch(
                            $filtersCopy,
                            $userIdCopy
                        );
                    })
                    ->catch(function (Batch $batch, Throwable $e) {

                        Log::error('Merge batch FAILED', [
                            'error' => $e->getMessage()
                        ]);
                    })
                    ->dispatch();
            })
            ->catch(function (Batch $batch, Throwable $e) {

                Log::error('Page batch FAILED', [
                    'error' => $e->getMessage()
                ]);
            })
            ->dispatch();

        Log::info('GenerateLokalitiBatchJob completed', [
            'execution_time_sec' => round(microtime(true) - $startTime, 2),
            'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
        ]);
    }
}