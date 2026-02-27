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
        $startTime = microtime(true);

        Log::info('===== GenerateLokalitiBatchJob START =====', [
            'filters' => $this->filters,
            'userId' => $this->userId,
            'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
        ]);

        $type = $this->filters['type'] ?? null;
        $series = $this->filters['series'] ?? null;
        $parlimen = $this->filters['parlimen'] ?? null;
        $dun = $this->filters['dun'] ?? null;
        $dm = $this->filters['dm'] ?? null;

        if (!$type || !$series || !$parlimen || !$dun || !$dm) {
            Log::error('Missing required filters.', $this->filters);
            return;
        }

        // -------------------------
        // Folder cleanup
        // -------------------------
        $folderPath = "pdfs/{$type}/{$series}/{$dm}";
        if (Storage::disk('public')->exists($folderPath)) {
            $files = Storage::disk('public')->files($folderPath);
            if (!empty($files)) {
                Storage::disk('public')->delete($files);
                Log::info("Deleted existing PDFs", ['deleted_count' => count($files)]);
            }
        }

        // -------------------------
        // Validate PRMAP
        // -------------------------
        if (!isset($this->PRMAP[$type][$series])) {
            Log::error('Invalid PRMAP combination', ['type' => $type, 'series' => $series]);
            return;
        }

        $selectedPRUYear = $this->PRMAP[$type][$series];

        // -------------------------
        // Fetch DMs
        // -------------------------
        $validDMs = DB::table('dm')
            ->whereYear('effective_from', '<=', $selectedPRUYear)
            ->where(function ($q) use ($selectedPRUYear) {
                $q->whereYear('effective_to', '>=', $selectedPRUYear)
                  ->orWhereNull('effective_to');
            })
            ->where('kod_dun', $dun)
            ->where('koddm', $dm)
            ->pluck('koddm')
            ->toArray();

        $validDMs = array_unique($validDMs); // remove duplicates

        if (empty($validDMs)) {
            Log::warning('No valid DMs found.');
            return;
        }

        // -------------------------
        // Fetch Lokaliti
        // -------------------------
        $validLokaliti = DB::table('lokaliti')
            ->whereIn('koddm', $validDMs)
            ->pluck('kod_lokaliti')
            ->toArray();

        $validLokaliti = array_unique($validLokaliti); // remove duplicates

        if (empty($validLokaliti)) {
            Log::warning('No lokaliti found.');
            return;
        }

        // -------------------------
        // Prepare per-lokaliti jobs
        // -------------------------
        $perPage = 200;
        $jobs = [];

        foreach ($validLokaliti as $kodLokaliti) {
            $totalRows = DB::table('pengundi')
                ->where('pilihan_raya_type', $type)
                ->where('pilihan_raya_series', $series)
                ->where('kod_lokaliti', $kodLokaliti)
                ->count();

            if ($totalRows === 0) {
                continue;
            }

            $totalPages = ceil($totalRows / $perPage);

            for ($page = 1; $page <= $totalPages; $page++) {
                $jobs[] = new GenerateSingleLokalitiPdfJob(
                    $this->filters,
                    $this->PRMAP,
                    $kodLokaliti,
                    $page,
                    $perPage
                );
            }
        }

        if (empty($jobs)) {
            Log::warning('No jobs created.');
            return;
        }

        Log::info('Jobs prepared', ['total_jobs' => count($jobs)]);

        // -------------------------
        // Dispatch batch safely
        // -------------------------
        $filtersCopy = $this->filters;
        $PRMAPCopy = $this->PRMAP;
        $userIdCopy = $this->userId;
        $typeCopy = $type;
        $seriesCopy = $series;
        $dmCopy = $dm;
        $uniqueLokalitiCopy = $validLokaliti;

        Bus::batch($jobs)
            ->then(function (Batch $batch) use (
                $uniqueLokalitiCopy,
                $typeCopy,
                $seriesCopy,
                $dmCopy,
                $filtersCopy,
                $PRMAPCopy,
                $userIdCopy
            ) {
                $mergeJobs = [];
                foreach ($uniqueLokalitiCopy as $kodLokaliti) {
                    $mergeJobs[] = new MergeLokalitiPdfJob(
                        $kodLokaliti,
                        [
                            'type' => $typeCopy,
                            'series' => $seriesCopy,
                            'dm' => $dmCopy,
                        ]
                    );
                }

                Bus::batch($mergeJobs)
                    ->then(function (Batch $mergeBatch) use ($filtersCopy, $PRMAPCopy, $userIdCopy) {
                        GenerateLokalitiSummaryPdfJob::dispatch($filtersCopy, $PRMAPCopy, $userIdCopy);
                    })
                    ->catch(function (Batch $mergeBatch, Throwable $e) {
                        Log::error('Merge batch FAILED', ['error' => $e->getMessage()]);
                    })
                    ->dispatch();
            })
            ->catch(function (Batch $batch, Throwable $e) {
                Log::error('Page batch FAILED', ['error' => $e->getMessage()]);
            })
            ->dispatch();

        Log::info('GenerateLokalitiBatchJob completed', [
            'execution_time_sec' => round(microtime(true) - $startTime, 2),
        ]);
    }
}