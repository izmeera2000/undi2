<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Throwable;

class GenerateSingleLokalitiPdfJobOptimized implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected array $filters;
    protected string $kod_lokaliti;
    protected string $nama_lokaliti;
    protected array $records; // All pengundi records for this lokaliti
    protected int $perPage;

    public $tries = 5;
    public $timeout = 600; // increased timeout for multiple pages

    public function __construct(array $filters, string $kod_lokaliti, string $nama_lokaliti, array $records, int $perPage = 22)
    {
        $this->filters = $filters;
        $this->kod_lokaliti = $kod_lokaliti;
        $this->nama_lokaliti = $nama_lokaliti;
        $this->records = $records;
        $this->perPage = $perPage;
    }

    public function handle()
    {
        try {
            $type = $this->filters['type'] ?? null;
            $series = $this->filters['series'] ?? null;
            $dm = $this->filters['dm'] ?? null;

            if (!$type || !$series || !$dm || empty($this->records)) {
                Log::warning("No data or missing filters for {$this->kod_lokaliti}");
                return;
            }

            $totalRows = count($this->records);
            $totalPages = ceil($totalRows / $this->perPage);
            $namaLokaliti = $this->records[0]->nama_lokaliti ?? '';

            // Initialize mPDF once
            $mpdf = new Mpdf([
                'format' => 'A4',
                'orientation' => 'P',
                'margin_top' => 35,
                'margin_bottom' => 20,
                'margin_left' => 10,
                'margin_right' => 10,
                'simpleTables' => true,
                'packTableData' => true,
            ]);

            // Set header and watermark once
            $header = View::make('pengundi.pdf.list_data_pdf_single_header', [
                'lokaliti' => $this->nama_lokaliti,
            ])->render();

            $mpdf->SetHTMLHeader($header);
            $mpdf->SetWatermarkImage(public_path('assets/img/UMNO_logo.png'));
            $mpdf->showWatermarkImage = true;
            $mpdf->watermarkImageAlpha = 0.1;
            $mpdf->watermarkImgBehind = true;

            // Loop through pages
            for ($page = 1; $page <= $totalPages; $page++) {
                $pageData = array_slice($this->records, ($page - 1) * $this->perPage, $this->perPage);
                $startNumber = ($page - 1) * $this->perPage + 1;

                $data = [
                    'kod_lokaliti' => $this->kod_lokaliti,
                    'nama_lokaliti' => $namaLokaliti,
                    'pilihan_raya_type' => $type,
                    'pilihan_raya_series' => $series,
                    'details' => $pageData,
                ];

                $html = View::make('pengundi.pdf.list_data_pdf_single', [
                    'data' => [$data],
                    'filters' => $this->filters,
                    'page' => $page,
                    'startNumber' => $startNumber,
                ])->render();

                $mpdf->WriteHTML($html);

                if ($page < $totalPages) {
                    $mpdf->AddPage();
                }
            }

            // Save PDF
            $folderPath = "pdfs/{$type}/{$series}/{$dm}";
            $fileName = "{$this->kod_lokaliti}.pdf"; // single PDF for all pages
            $fullPath = "{$folderPath}/{$fileName}";

            if (Storage::disk('public')->exists($fullPath)) {
                Log::info("PDF already exists, skipping", ['file' => $fullPath]);
                return;
            }

            $pdfContent = $mpdf->Output('', Destination::STRING_RETURN);
            Storage::disk('public')->put($fullPath, $pdfContent);

            // Cleanup
            unset($mpdf, $pdfContent);
            gc_collect_cycles();

            Log::info("Generated PDF for {$this->kod_lokaliti}", ['pages' => $totalPages]);

        } catch (Throwable $e) {
            Log::error("GenerateSingleLokalitiPdfJobOptimized failed for {$this->kod_lokaliti}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function failed(Throwable $exception)
    {
        Log::error("GenerateSingleLokalitiPdfJobOptimized permanently failed for {$this->kod_lokaliti}", [
            'error' => $exception->getMessage()
        ]);
    }
}