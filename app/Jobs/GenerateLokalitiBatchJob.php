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
use Throwable;

class GenerateLokalitiBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $filters;
    protected array $PRMAP;

    public function __construct(array $filters, array $PRMAP)
    {
        $this->filters = $filters;
        $this->PRMAP = $PRMAP; // ✅ now exists inside the queued job
    }

    public function handle()
    {
        Log::info('GenerateLokalitiBatchJob handle started', ['filters' => $this->filters, 'PRMAP' => $this->PRMAP]);




        $type = $this->filters['type'] ?? null;
        $series = $this->filters['series'] ?? null;
        $parlimen = $this->filters['parlimen'] ?? null;
        $dun = $this->filters['dun'] ?? null;
        $dm = $this->filters['dm'] ?? null;

        if (!$type || !$series || !$parlimen || !$dun || !$dm) {
            Log::error('Missing required filters.', $this->filters);
            return;
        }

        // -----------------------------
        // Validate PRMAP
        // -----------------------------
        if (!isset($this->PRMAP[$type][$series])) {
            Log::error('Invalid type/series.', ['type' => $type, 'series' => $series]);
            return;
        }

        $selectedPRUYear = $this->PRMAP[$type][$series];
        Log::info("Selected PRU Year determined.", ['type' => $type, 'series' => $series, 'year' => $selectedPRUYear]);

        // -----------------------------
        // Fetch valid DMs
        // -----------------------------
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

        Log::info('Valid DMs fetched.', ['validDMs' => $validDMs]);

        if (empty($validDMs)) {
            Log::warning('No valid DM found for the filters.', ['filters' => $this->filters]);
            return;
        }

        // -----------------------------
        // Fetch valid Lokaliti
        // -----------------------------
        $validLokaliti = DB::table('lokaliti')
            ->whereIn('koddm', $validDMs)
            ->pluck('kod_lokaliti')
            ->toArray();

        Log::info('Valid Lokaliti fetched.', ['validLokaliti' => $validLokaliti]);

        if (empty($validLokaliti)) {
            Log::warning('No valid Lokaliti found for the DMs.', ['validDMs' => $validDMs]);
            return;
        }

        // -----------------------------
        // Count total rows
        // -----------------------------
        $totalRows = DB::table('pengundi')
            ->where('pilihan_raya_type', $type)
            ->where('pilihan_raya_series', $series)
            ->whereIn('kod_lokaliti', $validLokaliti)
            ->count();

        Log::info("Total rows in pengundi table.", ['count' => $totalRows]);

        if ($totalRows === 0) {
            Log::warning("No data found for the given filters.", ['filters' => $this->filters]);
            return;
        }

        // -----------------------------
        // Prepare jobs
        // -----------------------------
        $perPage = 200;
        $totalPages = ceil($totalRows / $perPage);
        $jobs = [];

        Log::info("Preparing jobs.", ['totalPages' => $totalPages, 'perPage' => $perPage]);

        foreach ($validLokaliti as $kodLokaliti) {
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

        $jobCount = count($jobs);
        Log::info("Total jobs prepared.", ['jobCount' => $jobCount]);

        if ($jobCount === 0) {
            Log::warning('No jobs were created; exiting.');
            return;
        }

        $uniqueLokaliti = array_unique($validLokaliti);

        // -----------------------------
        // Dispatch batch
        // -----------------------------
        Log::info('Dispatching batch...');
        Bus::batch($jobs)
            ->then(function (Batch $batch) use ($uniqueLokaliti, $type, $series, $dm) {
                Log::info("Batch finished generating page PDFs.", ['batch_id' => $batch->id]);

                foreach ($uniqueLokaliti as $kodLokaliti) {
                    Log::info("Dispatching MergeLokalitiPdfJob.", ['kodLokaliti' => $kodLokaliti]);
                    MergeLokalitiPdfJob::dispatch(
                        $kodLokaliti,
                        [
                            'type' => $type,
                            'series' => $series,
                            'dm' => $dm,
                        ]
                    );
                }


            })
            ->catch(function (Batch $batch, Throwable $e) {
                Log::error("Batch failed.", ['message' => $e->getMessage()]);
            })
            ->finally(function (Batch $batch) {
                Log::info("Batch finally callback executed.", ['batch_id' => $batch->id]);
            })
            ->dispatch();

        GenerateLokalitiSummaryPdfJob::dispatch(
            $this->filters,
            $this->PRMAP,
        );




        Log::info("GenerateLokalitiBatchJob dispatch completed.");
    }
}