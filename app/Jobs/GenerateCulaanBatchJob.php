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
        // Fetch all relevant lokaliti
        // -------------------------
        $lokalitiList = CulaanPengundi::where('culaan_id', $culaanId)
            ->when($filters['lokaliti'] ?? null, fn($q, $lok) => $q->where('lokaliti', 'like', "%$lok%"))
            ->when($filters['status_culaan'] ?? null, fn($q, $status) => $q->where('status_culaan', 'like', "$status%"))
            ->when($filters['search_name'] ?? null, function ($q, $search) {
                $q->where('nama', 'like', "%$search%")
                    ->orWhere('no_kp', 'like', "%$search%");
            })
            ->pluck('kod_lokaliti')
            ->unique()
            ->toArray();

        if (empty($lokalitiList)) {
            Log::warning('No lokaliti found for batch job', compact('culaanId', 'filters'));
            return;
        }

        // -------------------------
        // Create per-lokaliti PDF jobs
        // -------------------------
        $perPage = 200;
        $jobs = [];

        foreach ($lokalitiList as $kodLokaliti) {
            $totalRows = CulaanPengundi::where('culaan_id', $culaanId)
                ->where('kod_lokaliti', $kodLokaliti)
                ->count();

            if ($totalRows === 0)
                continue;

            $totalPages = ceil($totalRows / $perPage);

            for ($page = 1; $page <= $totalPages; $page++) {
                $jobs[] = new GenerateSingleCulaanPdfJob(
                    $culaanId,
                    $filters,
                    $kodLokaliti,
                    $page,
                    $perPage
                );
            }
        }

        if (empty($jobs)) {
            Log::warning('No per-lokaliti jobs created', compact('culaanId', 'filters'));
            return;
        }

        // -------------------------
        // Dispatch batch with merge callback
        // -------------------------
        Bus::batch($jobs)
            ->then(function (Batch $batch) use ($lokalitiList, $culaanId, $filters, $userId) {
                Log::info('All per-lokaliti jobs finished, now merging PDFs');

                MergeCulaanPdfJob::dispatch($culaanId, $filters, $userId);
            })
            ->catch(function (Batch $batch, Throwable $e) {
                Log::error('Per-lokaliti batch FAILED', ['error' => $e->getMessage()]);
            })
            ->dispatch();

        Log::info('GenerateCulaanBatchJob dispatched successfully');
    }
}