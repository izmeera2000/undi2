<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Log;

class MergeLokalitiPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $kodLokaliti;
    protected $filters; // type, series, dm

    public function __construct($kodLokaliti, array $filters)
    {
        $this->kodLokaliti = $kodLokaliti;
        $this->filters = $filters;
    }

    public function handle()
    {
        // Build folder path based on structured hierarchy
        $baseFolder = "pdfs/{$this->filters['type']}/{$this->filters['series']}/{$this->filters['dm']}";
        // Get all page PDFs in this lokaliti folder
        Log::info("Merging PDFs in folder: $baseFolder");

        $lokalitiPages = collect(Storage::disk('public')->files($baseFolder))
            ->filter(fn($file) => str_contains($file, $this->kodLokaliti))
            ->sort();

        Log::info("Files found for merge:", $lokalitiPages->toArray());
        if ($lokalitiPages->isEmpty())
            return;

        $pdf = new Fpdi();

        // Merge each page PDF
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

        // Save merged PDF in the same folder
        $mergedRelativePath = "{$baseFolder}/{$this->kodLokaliti}_merged.pdf";
        $mergedAbsolutePath = Storage::disk('public')->path($mergedRelativePath);

        $pdf->Output($mergedAbsolutePath, 'F');

        // Delete page PDFs after merging
        foreach ($lokalitiPages as $file) {
            Storage::disk('public')->delete($file);
        }
    }
 
}