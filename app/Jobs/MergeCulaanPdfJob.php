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
use Illuminate\Bus\Batchable;

use App\Models\User;
use App\Notifications\CulaanPdfReadyNotification;



class MergeCulaanPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

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
        $files = Storage::disk('public')->files($folderPath);

        $filesToMerge = array_filter(
            $files,
            fn($f) =>
            str_ends_with($f, '.pdf') &&
            str_contains($f, 'temp_') &&
            str_contains($f, "culaan_{$this->culaanId}_") &&
            (
                str_contains($f, '_summary') ||
                str_contains($f, '_page')
            )
        );

        usort($filesToMerge, function ($a, $b) {

            $aIsSummary = str_contains($a, '_summary');
            $bIsSummary = str_contains($b, '_summary');

            if ($aIsSummary && !$bIsSummary)
                return -1;
            if (!$aIsSummary && $bIsSummary)
                return 1;

            return strnatcmp($a, $b); // natural page sorting
        });

        if (empty($filesToMerge)) {
            Log::warning("No PDF files to merge for Culaan {$this->culaanId}");
            return;
        }

        $fpdi = new Fpdi();

        foreach ($filesToMerge as $file) {
            $filePath = Storage::disk('public')->path($file);
            $pageCount = $fpdi->setSourceFile($filePath);
            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $fpdi->importPage($i);
                $size = $fpdi->getTemplateSize($tpl);
                $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $fpdi->useTemplate($tpl);
            }
        }

        // Save final merged PDF
        $lokaliti = !empty($this->filters['lokaliti'])
            ? preg_replace('/[^A-Za-z0-9]/', '_', $this->filters['lokaliti'])
            : 'all';

        $status = $this->filters['status_culaan'] ?? 'all';

        $search = !empty($this->filters['search_name'])
            ? preg_replace('/[^A-Za-z0-9]/', '_', $this->filters['search_name'])
            : 'all';

        $fileName = "culaan_{$this->culaanId}_lokaliti_{$lokaliti}_status_{$status}_search_{$search}.pdf";

        $mergedPath = "{$folderPath}/{$fileName}";


        $fpdi->Output(Storage::disk('public')->path($mergedPath), 'F');

        // Delete original per-lokaliti PDFs
        foreach ($filesToMerge as $file) {
            Storage::disk('public')->delete($file);
        }

        $user = User::find($this->userId);

        if ($user) {
            $url = asset('storage/' . $mergedPath);
            $user->notify(new CulaanPdfReadyNotification($url, $this->culaanId));
        }

        Log::info("All lokaliti PDFs merged into: {$mergedPath} and originals deleted");
    }
}