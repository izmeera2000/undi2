<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Batchable;
use Mpdf\Mpdf;

class GenerateSingleCulaanPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected int $culaanId;
    protected array $filters;

    protected int $globalPage;
    protected int $pmPage;

    protected int $perPage;

    protected string $pm;

    protected int $rowStart;

    public function __construct(
        int $culaanId,
        array $filters,
        int $globalPage,
        int $pmPage,
        int $perPage,
        string $pm,
        int $rowStart
    ) {
        $this->culaanId = $culaanId;
        $this->filters = $filters;

        $this->globalPage = $globalPage;
        $this->pmPage = $pmPage;

        $this->perPage = $perPage;

        $this->pm = $pm;

        $this->rowStart = $rowStart;
    }

    public function handle()
    {

        Log::info("GenerateSingleCulaanPdfJob START", [
            'culaan_id' => $this->culaanId,
            'pm' => $this->pm,
            'global_page' => $this->globalPage,
            'pm_page' => $this->pmPage,
            'row_start' => $this->rowStart
        ]);

        /*
        |--------------------------------------------------------------------------
        | Status Mapping
        |--------------------------------------------------------------------------
        */

        $statuses = [
            'D' => 'BN',
            'A' => 'PH',
            'C' => 'PAS',
            'E' => 'TP',
            'O' => 'BC'
        ];

        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query = DB::table('culaan_pengundis')
            ->select([
                'id',
                'nama',
                'no_kp',
                'pm',
                'lokaliti',
                'kod_lokaliti',
                'kategori_pengundi',
                'status_pengundi',
                'status_culaan'
            ])
            ->where('culaan_id', $this->culaanId)
            ->where('pm', $this->pm);

        if (!empty($this->filters['lokaliti'])) {
            $query->where('kod_lokaliti', 'like', "%{$this->filters['lokaliti']}%");
        }

        if (!empty($this->filters['status_culaan'])) {
            $query->where('status_culaan', 'like', $this->filters['status_culaan'] . '%');
        }

        if (!empty($this->filters['search_name'])) {

            $search = trim($this->filters['search_name']);

            $query->where(function ($q) use ($search) {

                if (str_starts_with($search, '*') && str_ends_with($search, '*')) {
                    $pattern = '%' . substr($search, 1, -1) . '%';
                } elseif (str_starts_with($search, '*')) {
                    $pattern = '%' . substr($search, 1);
                } elseif (str_ends_with($search, '*')) {
                    $pattern = substr($search, 0, -1) . '%';
                } else {
                    $pattern = '%' . $search . '%';
                }

                $q->where('nama', 'like', $pattern)
                  ->orWhere('no_kp', 'like', $pattern);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination (IMPORTANT)
        |--------------------------------------------------------------------------
        */

        $rows = $query
            ->orderBy('id')
            ->offset(($this->pmPage - 1) * $this->perPage)
            ->limit($this->perPage)
            ->get();

        Log::info("Rows loaded", [
            'count' => $rows->count(),
            'global_page' => $this->globalPage,
            'pm_page' => $this->pmPage
        ]);

        if ($rows->isEmpty()) {

            Log::warning("No rows found", [
                'pm' => $this->pm,
                'pm_page' => $this->pmPage
            ]);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Initialize mPDF
        |--------------------------------------------------------------------------
        */

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_top' => 35,
            'margin_bottom' => 20,
            'margin_left' => 10,
            'margin_right' => 10
        ]);

        $mpdf->simpleTables = true;
        $mpdf->packTableData = true;

        $mpdf->SetHTMLHeader("
        <div style='font-weight:bold;font-size:14px;background:#f0f0f0;padding:6px;border:1px solid #ccc'>
            PM: {$this->pm} | Page {$this->globalPage}
        </div>
        ");

        /*
        |--------------------------------------------------------------------------
        | Table
        |--------------------------------------------------------------------------
        */

        $html = '
        <table width="100%" border="1" cellspacing="0" cellpadding="4"
        style="border-collapse:collapse;font-size:11px;table-layout:fixed">

        <thead>
        <tr style="background:#e8e8e8">
            <th width="8%">No</th>
            <th width="35%">Pengundi</th>
            <th width="25%">Lokaliti</th>
            <th width="20%">Details</th>
            <th width="12%">Culaan</th>
        </tr>
        </thead>
        <tbody>';

        $counter = $this->rowStart;

        foreach ($rows as $row) {

            $statusCode = $row->status_culaan
                ? strtoupper(substr(trim($row->status_culaan), 0, 1))
                : 'O';

            $status = $statuses[$statusCode] ?? $statusCode;

            $lokaliti = $row->lokaliti . ' (' . $row->kod_lokaliti . ')';

            $details = $row->kategori_pengundi .
                ($row->status_pengundi ? " ({$row->status_pengundi})" : '');

            $html .= "
            <tr>
                <td>{$counter}<br>ID :{$row->id}</td>

                <td>
                    <strong>{$row->nama}</strong><br>
                    <small>{$row->no_kp}</small>
                </td>

                <td>{$lokaliti}</td>

                <td>{$details}</td>

                <td style='text-align:center'>{$status}</td>
            </tr>";

            $counter++;
        }

        $html .= '</tbody></table>';

        $mpdf->WriteHTML($html);

        Log::info("PDF table rendered");

        /*
        |--------------------------------------------------------------------------
        | File Naming
        |--------------------------------------------------------------------------
        */

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

        $fileName = "temp_culaan_{$this->culaanId}_lokaliti_{$lokaliti}_status_{$status}_search_{$search}_pm_{$safePm}_page{$this->globalPage}.pdf";

        $filePath = "pdfs/culaan/{$this->culaanId}/{$fileName}";

        Storage::disk('public')->put($filePath, $mpdf->Output('', 'S'));

        Log::info("PDF saved", [
            'file' => $filePath
        ]);

        unset($mpdf);
        gc_collect_cycles();

        Log::info("GenerateSingleCulaanPdfJob FINISHED");
    }
}