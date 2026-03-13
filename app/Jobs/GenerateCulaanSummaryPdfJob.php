<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GenerateCulaanSummaryPdfJob implements ShouldQueue
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
        $folderPath = "pdfs/culaan/{$this->culaanId}";
        $mergedFiles = Storage::disk('public')->files($folderPath);
        $mergedFiles = array_filter($mergedFiles, fn($f) => str_contains($f, '_merged.pdf'));

        if (empty($mergedFiles)) {
            Log::warning("No merged PDFs to generate summary for Culaan {$this->culaanId}");
            return;
        }

        $culaan = DB::table('culaans')->find($this->culaanId);

        $pdf = Pdf::loadView('culaan.culaan_summary_pdf', [
            'culaan' => $culaan,
            'mergedFiles' => $mergedFiles,
        ])->setPaper('a4', 'landscape');

        $summaryPath = "{$folderPath}/culaan_summary.pdf";
        Storage::disk('public')->put($summaryPath, $pdf->output());

        Log::info("Summary PDF generated: {$summaryPath}");
    }
}