<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Throwable;

class GenerateSingleCulaanPmPdfJobOptimized implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected int $culaanId;
    protected array $filters;
    protected string $pm;
    protected array $records;
    protected int $perPage;

    public $tries = 5;
    public $timeout = 600;

    public function __construct(int $culaanId, array $filters, string $pm, array $records, int $perPage = 22)
    {
        $this->culaanId = $culaanId;
        $this->filters = $filters;
        $this->pm = $pm;
        $this->records = $records;
        $this->perPage = $perPage;
    }

    public function handle()
    {
        try {

            Log::info("JOB START", [
                'pm' => $this->pm,
                'culaan_id' => $this->culaanId,
                'records' => count($this->records),
                'job_id' => optional($this->job)->getJobId()
            ]);

            if (empty($this->records)) {
                Log::warning("No records for PM {$this->pm}");
                return;
            }

            $totalRows = count($this->records);
            $totalPages = (int) ceil($totalRows / $this->perPage);

            $batchId = $this->batch()?->id ?? 'default';

            $globalRowKey = "culaan_{$this->culaanId}_{$batchId}_global_row";
            $globalPageKey = "culaan_{$this->culaanId}_{$batchId}_global_page";

            // ----------------------
            // Read cache
            // ----------------------
            $globalRow = Cache::get($globalRowKey, 1);
            $globalPage = Cache::get($globalPageKey, 1);

            Log::info("CACHE READ", [
                'pm' => $this->pm,
                'globalRow' => $globalRow,
                'globalPage' => $globalPage,
                'row_key' => $globalRowKey,
                'page_key' => $globalPageKey
            ]);

            // ----------------------
            // Initialize mPDF
            // ----------------------
            $mpdf = new Mpdf([
                'format' => 'A4',
                'margin_top' => 35,
                'margin_bottom' => 20,
                'margin_left' => 10,
                'margin_right' => 10,
                'simpleTables' => true,
                'packTableData' => true,
            ]);

            $mpdf->SetWatermarkImage(public_path('assets/img/UMNO_logo.png'));
            $mpdf->showWatermarkImage = true;
            $mpdf->watermarkImageAlpha = 0.1;
            $mpdf->watermarkImgBehind = true;

            // ----------------------
            // Generate pages
            // ----------------------
            for ($page = 1; $page <= $totalPages; $page++) {

                $pageData = array_slice(
                    $this->records,
                    ($page - 1) * $this->perPage,
                    $this->perPage
                );

                Log::info("COUNTER BEFORE", [
                    'globalPage' => $globalPage
                ]);

                // ✅ Generate header per page
                $header = view('culaan.culaan_pdf_header', [
                    'pm' => $this->pm,
                    'page' => $globalPage
                ])->render();

                // ✅ UNIQUE header name per page
                $headerName = 'header_' . $globalPage;

                // ✅ DEFINE header (important!)
                $mpdf->DefHTMLHeaderByName($headerName, $header);

                if ($page > 1) {
                    // ✅ SET header FIRST, then AddPage
                    $mpdf->SetHTMLHeaderByName($headerName);
                    $mpdf->AddPage();
                } else {
                    $mpdf->SetHTMLHeaderByName($headerName);
                }

                $html = view('culaan.culaan_pdf', [
                    'rows' => $pageData,
                    'counter' => $globalRow,
                    'pm' => $this->pm
                ])->render();

                $mpdf->WriteHTML($html);

                $globalRow += count($pageData);
                $globalPage++;
            }

            // ----------------------
            // Save PDF
            // ----------------------

            $dm = !empty($this->filters['dm'])
                ? preg_replace('/[^A-Za-z0-9]/', '_', $this->filters['dm'])
                : 'all';

            $lokaliti = !empty($this->filters['lokaliti'])
                ? preg_replace('/[^A-Za-z0-9]/', '_', $this->filters['lokaliti'])
                : 'all';

            $status = !empty($this->filters['status_culaan'])
                ? preg_replace('/[^A-Za-z0-9]/', '_', $this->filters['status_culaan'])
                : 'all';

            $search = !empty($this->filters['search_name'])
                ? preg_replace('/[^A-Za-z0-9]/', '_', $this->filters['search_name'])
                : 'all';

            $safePm = preg_replace('/[^A-Za-z0-9]/', '_', $this->pm);

            $fileName = "temp_culaan_{$this->culaanId}_dm_{$dm}_lokaliti_{$lokaliti}_status_{$status}_search_{$search}_pm_{$safePm}_page{$globalPage}.pdf";
            $filePath = "pdfs/culaan/{$this->culaanId}/{$fileName}";

            $pdfContent = $mpdf->Output('', Destination::STRING_RETURN);
            Storage::disk('public')->put($filePath, $pdfContent);

            Log::info("PDF SAVED", [
                'pm' => $this->pm,
                'file' => $filePath
            ]);

            unset($mpdf, $pdfContent);
            gc_collect_cycles();

            // ----------------------
            // Metadata
            // ----------------------
            $metadataKey = "culaan_{$this->culaanId}_{$batchId}_pm_metadata";

            $metadata = Cache::get($metadataKey, []);

            Log::info("METADATA READ", [
                'pm' => $this->pm,
                'metadata_key' => $metadataKey,
                'existing_keys' => array_keys($metadata)
            ]);

            $metadata[$this->pm] = [
                'pm' => $this->pm,
                'total_rows' => $totalRows,
                'total_pages' => $totalPages,
                'start_page' => $globalPage - $totalPages,
                'file' => $filePath
            ];

            Log::info("METADATA WRITE", [
                'pm' => $this->pm,
                'start_page' => $globalPage - $totalPages
            ]);

            Cache::put($metadataKey, $metadata, 3600);

            // ----------------------
            // Save global counters
            // ----------------------
            Log::info("CACHE WRITE", [
                'pm' => $this->pm,
                'globalRow_final' => $globalRow,
                'globalPage_final' => $globalPage
            ]);

            Cache::put($globalRowKey, $globalRow, 3600);
            Cache::put($globalPageKey, $globalPage, 3600);

            Log::info("JOB FINISHED", [
                'pm' => $this->pm,
                'total_rows' => $totalRows,
                'total_pages' => $totalPages
            ]);

        } catch (Throwable $e) {

            Log::error("JOB FAILED", [
                'pm' => $this->pm,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }



}