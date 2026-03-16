<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Models\CulaanPengundi;
use Illuminate\Contracts\Queue\ShouldBeUnique;


class GenerateCulaanBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $culaanId;
    protected array $filters;
    protected int $userId;

    public function __construct(int $culaanId, array $filters, int $userId)
    {
        $this->culaanId = $culaanId;
        $this->filters = $filters;
        $this->userId = $userId;
    }


    public function handle()
    {
        $culaanId = $this->culaanId;
        $filters = $this->filters;
        $userId = $this->userId;
        Log::info('Starting GenerateCulaanBatchJob', compact('culaanId', 'filters', 'userId'));


        // -------------------------
        // Build filtered query
        // -------------------------
// Base Query
// -------------------------

        $query = CulaanPengundi::where('culaan_id', $culaanId)

            ->when($filters['lokaliti'] ?? null, function ($q, $lok) {
                $q->where('kod_lokaliti', 'like', "%{$lok}%");
            })

            ->when($filters['status_culaan'] ?? null, function ($q, $status) {
                $q->where('status_culaan', 'like', "{$status}%");
            })

            ->when($filters['search_name'] ?? null, function ($q, $search) {

                $search = trim($search);

                $q->where(function ($qq) use ($search) {

                    if (str_starts_with($search, '*') && str_ends_with($search, '*')) {
                        $pattern = '%' . substr($search, 1, -1) . '%';
                    } elseif (str_starts_with($search, '*')) {
                        $pattern = '%' . substr($search, 1);
                    } elseif (str_ends_with($search, '*')) {
                        $pattern = substr($search, 0, -1) . '%';
                    } else {
                        $pattern = '%' . $search . '%';
                    }

                    $qq->where('nama', 'like', $pattern)
                        ->orWhere('no_kp', 'like', $pattern);

                });

            })

            ->orderBy('pm', 'asc');


        // -------------------------
// Get all unique PMs
// -------------------------

        $pms = (clone $query)
            ->select('pm')
            ->distinct()
            ->pluck('pm');

        Log::info('PM list retrieved', [
            'culaan_id' => $culaanId,
            'pm_count' => $pms->count(),
            'pms' => $pms->toArray()
        ]);

        if ($pms->isEmpty()) {

            Log::warning('No culaan pengundi found for batch job', compact('culaanId', 'filters'));
            return;

        }


        // -------------------------
// Create jobs per PM
// -------------------------

        $jobs = [];
        $perPage = 22;

        $globalPage = 1;
        $globalRow = 1;
        $toc = [];

        foreach ($pms as $pm) {

            $pmQuery = (clone $query)->where('pm', $pm);

            $totalRows = $pmQuery->count();

            if ($totalRows === 0) {
                continue;
            }

            $toc[] = [
                'pm' => $pm,
                'start_page' => $globalPage,
                'total_rows' => $totalRows,
            ];


            $totalPages = (int) ceil($totalRows / $perPage);

            for ($page = 1; $page <= $totalPages; $page++) {

                $rowsThisPage = min($perPage, $totalRows - (($page - 1) * $perPage));

                $jobs[] = new GenerateSingleCulaanPdfJob(
                    $culaanId,
                    $filters,
                    $globalPage,
                    $page,
                    $perPage,
                    $pm,
                    $globalRow
                );

                Log::info("Prepared GenerateSingleCulaanPdfJob", [
                    'culaan_id' => $culaanId,
                    'pm' => $pm,
                    'global_page' => $globalPage,
                    'pm_page' => $page,
                    'per_page' => $perPage,
                    'start_row' => $globalRow,
                    'rows_this_page' => $rowsThisPage
                ]);

                $globalRow += $rowsThisPage;
                $globalPage++;
            }
        }
        Log::info('Table of Contents prepared', ['toc' => $toc]);
        // -------------------------
// Dispatch jobs
// -------------------------

        if (empty($jobs)) {

            Log::warning('No PDF jobs created', compact('culaanId', 'filters'));
            return;

        }

        foreach ($jobs as $job) {
            dispatch($job);
        }

        Log::info('All GenerateSingleCulaanPdfJobs dispatched', [
            'culaan_id' => $culaanId,
            'filters' => $filters,
            'total_pages' => $globalPage - 1,
            'final_row' => $globalRow
        ]);
        // -------------------------
        // Dispatch batch with merge callback
        // -------------------------
        Bus::batch($jobs)
            ->then(function (Batch $batch) use ($culaanId, $filters, $userId) {
                Log::info('All per-lokaliti jobs finished, now merging PDFs');
                GenerateCulaanSummaryPdfJob::dispatch($culaanId, $filters, $userId);

                MergeCulaanPdfJob::dispatch($culaanId, $filters, $userId);
            })
            ->catch(function (Batch $batch, Throwable $e) {
                Log::error('Per-lokaliti batch FAILED', ['error' => $e->getMessage()]);
            })
            ->dispatch();

        Log::info('GenerateCulaanBatchJob dispatched successfully');
    }



}