<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Models\CulaanPengundi;
use Illuminate\Support\Facades\Cache;

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
        $query = CulaanPengundi::where('culaan_id', $culaanId)
            ->when($filters['lokaliti'] ?? null, fn($q, $lok) => $q->where('kod_lokaliti', 'like', "%{$lok}%"))
            ->when($filters['status_culaan'] ?? null, fn($q, $status) => $q->where('status_culaan', 'like', "{$status}%"))
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
        // Get unique PMs
        // -------------------------
        $pms = (clone $query)->select('pm')->distinct()->pluck('pm');

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
        // Dispatch PM jobs (no globalPage)
        // -------------------------
        $jobs = [];
        $perPage = 22;

        foreach ($pms as $pm) {

            $records = (clone $query)
                ->where('pm', $pm)
                ->orderBy('id')
                ->get()
                ->toArray();

            if (empty($records)) {
                continue;
            }

            $jobs[] = new GenerateSingleCulaanPmPdfJobOptimized(
                $culaanId,
                $filters,
                $pm,
                $records,
                $perPage
            );
        }

        if (empty($jobs)) {
            Log::warning('No PDF jobs created', compact('culaanId', 'filters'));
            return;
        }

        // -------------------------
        // Dispatch as a batch
        // -------------------------
        Bus::batch($jobs)
            ->then(function ($batch) use ($culaanId, $filters, $userId) {
                $batchId = $batch->id;

                // PM metadata cache key using batch ID
                $metadataKey = "culaan_{$culaanId}_{$batchId}_pm_metadata";
 
                $toc = Cache::get($metadataKey, []);

                Log::info('TOC ready for merging', [
                    'toc' => $toc,
                    'batch_id' => $batchId
                ]);

                // Dispatch PDF merge / summary
                GenerateCulaanSummaryPdfJob::dispatch($culaanId, $filters, $userId, $toc);
                MergeCulaanPdfJob::dispatch($culaanId, $filters, $userId);



       


            })
            ->catch(function ($batch, Throwable $e) {
                Log::error('Per-PM batch failed', ['error' => $e->getMessage()]);
            })
            ->dispatch();

        Log::info('GenerateCulaanBatchJob dispatched successfully');
    }
}