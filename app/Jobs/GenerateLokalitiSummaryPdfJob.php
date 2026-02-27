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

        if (!$type || !$series || !isset($this->PRMAP[$type][$series])) {
            Log::error('Missing type/series or PRMAP not found', $this->filters);
            return;
        }

        $selectedPRUYear = $this->PRMAP[$type][$series];
        $selectedPRUDate = $selectedPRUYear . '-12-31';

        Log::info('PRU Year determined', [
            'year' => $selectedPRUYear,
            'date' => $selectedPRUDate
        ]);

        // -------------------------------
        // Fetch pengundi with latest effective DM
        // -------------------------------
        $pengundi = DB::table('pengundi')
            ->join('lokaliti', function($join) use ($selectedPRUDate) {
                $join->on('pengundi.kod_lokaliti', '=', 'lokaliti.kod_lokaliti')
                     ->where('lokaliti.effective_from', '<=', $selectedPRUDate)
                     ->where(function($q) use ($selectedPRUDate) {
                         $q->whereNull('lokaliti.effective_to')
                           ->orWhere('lokaliti.effective_to', '>=', $selectedPRUDate);
                     });
            })
            ->join('dm', function($join) use ($selectedPRUDate) {
                $join->on('lokaliti.koddm', '=', 'dm.koddm')
                     ->where('dm.effective_from', '<=', $selectedPRUDate)
                     ->where(function($q) use ($selectedPRUDate) {
                         $q->whereNull('dm.effective_to')
                           ->orWhere('dm.effective_to', '>=', $selectedPRUDate);
                     })
                     ->whereRaw('dm.effective_from = (
                         SELECT MAX(effective_from)
                         FROM dm AS sub
                         WHERE sub.koddm = dm.koddm
                           AND sub.effective_from <= ?
                           AND (sub.effective_to IS NULL OR sub.effective_to >= ?)
                     )', [$selectedPRUDate, $selectedPRUDate]);
            })
            ->join('dun', 'dm.kod_dun', '=', 'dun.kod_dun')
            ->join('parlimen', 'dun.parlimen_id', '=', 'parlimen.id')
            ->where('pengundi.pilihan_raya_type', $type)
            ->where('pengundi.pilihan_raya_series', $series)
            ->where('dun.parlimen_id', $this->filters['parlimen'])
            ->where('dm.kod_dun', $this->filters['dun'])
            ->where('lokaliti.koddm', $this->filters['dm'])
            ->select('pengundi.kod_lokaliti', 'lokaliti.nama_lokaliti', 'pengundi.saluran')
            ->distinct()
            ->get();

        if ($pengundi->isEmpty()) {
            Log::warning('No pengundi found');
            return;
        }

        // -------------------------------
        // Aggregate per lokaliti
        // -------------------------------
$rows = $pengundi
    ->groupBy('kod_lokaliti')
    ->map(function($group, $kod_lokaliti) use ($type, $series) {
        $row = [
            'kod_lokaliti' => $kod_lokaliti,
            'nama_lokaliti' => $group[0]->nama_lokaliti,
        ];

        for ($i = 1; $i <= 7; $i++) {
            $row["saluran_$i"] = 0;
        }

        foreach ($group as $p) {
            if ($p->saluran >= 1 && $p->saluran <= 7) {
                $row["saluran_{$p->saluran}"]++;
            }
        }

        $row['total'] = array_sum(array_map(fn($i) => $row["saluran_$i"], range(1,7)));

        // Convert to object
        return (object) $row;
    })->values();

        // -------------------------------
        // Generate PDF
        // -------------------------------
        $pdf = Pdf::loadView('pengundi.pdf.list_data_pdf', [
            'data' => $rows,
            'filters' => $this->filters
        ])->setPaper('a4', 'landscape');

        $folderPath = "pdfs/{$type}/{$series}/{$this->filters['dm']}";
        $fileName = "{$this->filters['dm']}_summary.pdf";
        $fullPath = "{$folderPath}/{$fileName}";

        Storage::disk('public')->put($fullPath, $pdf->output());

        Log::info('Summary PDF saved', ['path' => $fullPath]);

        // Notify user
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