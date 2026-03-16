<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Log;
use Throwable;

class MergeLokalitiPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $kodLokaliti;
    protected $filters; // type, series, dm

    public function __construct($kodLokaliti, array $filters)
    {
        $this->kodLokaliti = $kodLokaliti;
        $this->filters = $filters;
    }

    public function handle()
    {
        Log::info("Merge job START", ['lokaliti' => $this->kodLokaliti]);

        try {
            $baseFolder = "pdfs/{$this->filters['type']}/{$this->filters['series']}/{$this->filters['dm']}";

            $lokalitiPages = collect(Storage::disk('public')->files($baseFolder))
                ->filter(fn($file) => str_contains($file, $this->kodLokaliti))
                ->sort();

            Log::info("Files found for merge", ['files' => $lokalitiPages->toArray()]);

            if ($lokalitiPages->isEmpty()) {
                Log::warning("No page PDFs found for lokaliti", ['lokaliti' => $this->kodLokaliti]);
                return;
            }

            $pdf = new Fpdi();

            foreach ($lokalitiPages as $file) {
                $path = Storage::disk('public')->path($file);
                $pageCount = $pdf->setSourceFile($path);

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $tpl = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($tpl);

                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($tpl);
                }
            }
            $now = now()->timestamp;

            $mergedRelativePath = "{$baseFolder}/pengundi_list_{$this->kodLokaliti}_{$now}.pdf";
            $mergedAbsolutePath = Storage::disk('public')->path($mergedRelativePath);

            $pdf->Output($mergedAbsolutePath, 'F');

            // Delete page PDFs after merge
            foreach ($lokalitiPages as $file) {
                Storage::disk('public')->delete($file);
            }

            Log::info("Merge job SUCCESS", ['lokaliti' => $this->kodLokaliti]);

        } catch (\Throwable $e) {
            Log::error("Merge job FAILED", [
                'lokaliti' => $this->kodLokaliti,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // VERY important to fail the job properly
        }
    }

    public function failed(Throwable $exception)
    {
        Log::error('MergeLokalitiPdfJob failed callback', [
            'lokaliti' => $this->kodLokaliti,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }

}