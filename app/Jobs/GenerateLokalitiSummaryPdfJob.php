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
use Mpdf\Mpdf;
use App\Models\User;
use App\Notifications\LokalitiPdfGenerated;
use Throwable;

class GenerateLokalitiSummaryPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected array $filters;
    protected int $userId;

    public function __construct(array $filters, int $userId)
    {
        $this->filters = $filters;
        $this->userId = $userId;
    }

    public function handle()
    {
        Log::info('Summary Job STARTED', [
            'filters' => $this->filters,
            'userId' => $this->userId,
            'memory_start_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
        ]);



        $startTime = microtime(true);

        try {

            $type = $this->filters['type'] ?? null;
            $series = isset($this->filters['series']) ? (int) $this->filters['series'] : null;
            $dun = $this->filters['dun'] ?? null;
            $dm = $this->filters['dm'] ?? null;
            $parlimen = $this->filters['parlimen'] ?? null;

            if (!$type || !$series || !$dun || !$dm || !$parlimen) {
                Log::error('Missing required filters', $this->filters);
                return;
            }

            // --------------------------------
            // Fetch election year from DB
            // --------------------------------
            $selectedPRUYear = DB::table('elections')
                ->where('type', $type)
                ->where('number', $series)
                ->value('year');

            if (!$selectedPRUYear) {
                Log::error('Election not found in DB', [
                    'type' => $type,
                    'series' => $series
                ]);
                return;
            }


            $selectedPRUDate = $selectedPRUYear . '-12-31';


            $areaInfo = DB::table('dm as d')
                ->join('dun as dn', 'd.kod_dun', '=', 'dn.kod_dun')
                ->join('parlimen as p', 'dn.parlimen_id', '=', 'p.id')

                ->select(
                    'd.koddm',
                    'd.namadm',
                    'dn.kod_dun',
                    'dn.namadun',
                    'p.id as parlimen_id',
                    'p.namapar'
                )

                ->where('d.koddm', $dm)
                ->where('dn.kod_dun', $dun)

                // DM validity
                ->whereYear('d.effective_from', '<=', $selectedPRUYear)
                ->where(function ($q) use ($selectedPRUYear) {
                    $q->whereNull('d.effective_to')
                        ->orWhereYear('d.effective_to', '>=', $selectedPRUYear);
                })

                // DUN validity
                ->whereYear('dn.effective_from', '<=', $selectedPRUYear)
                ->where(function ($q) use ($selectedPRUYear) {
                    $q->whereNull('dn.effective_to')
                        ->orWhereYear('dn.effective_to', '>=', $selectedPRUYear);
                })

                ->first();

            $distinctSaluran = DB::table('pengundi')
                ->where('pilihan_raya_type', $type)
                ->where('pilihan_raya_series', $series)
                ->where('kod_lokaliti', '!=', null) // optional: only valid lokaliti
                ->distinct()
                ->orderBy('saluran')
                ->pluck('saluran')
                ->toArray();


            $saluranColumns = [];
            foreach ($distinctSaluran as $s) {
                $saluranColumns[] = "SUM(CASE WHEN p.saluran = {$s} THEN 1 ELSE 0 END) AS saluran_{$s}";
            }

            $saluranSelectRaw = implode(",\n", $saluranColumns);



            Log::info('Running aggregation query...', [
                'memory_before_query_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
            ]);

            $rows = DB::table(function ($query) use ($type, $series, $parlimen, $dun, $dm, $selectedPRUDate) {
                $query->from('pengundi as p')
                    ->select('p.id', 'p.kod_lokaliti', 'p.saluran', 'l.nama_lokaliti')
                    ->distinct()
                    ->join('lokaliti as l', function ($join) use ($selectedPRUDate) {
                        $join->on('p.kod_lokaliti', '=', 'l.kod_lokaliti')
                            ->where('l.effective_from', '<=', $selectedPRUDate)
                            ->where(function ($q) use ($selectedPRUDate) {
                                $q->whereNull('l.effective_to')
                                    ->orWhere('l.effective_to', '>=', $selectedPRUDate);
                            });
                    })
                    ->join('dm as d', function ($join) use ($selectedPRUDate) {
                        $join->on('l.koddm', '=', 'd.koddm')
                            ->where('d.effective_from', '<=', $selectedPRUDate)
                            ->where(function ($q) use ($selectedPRUDate) {
                                $q->whereNull('d.effective_to')
                                    ->orWhere('d.effective_to', '>=', $selectedPRUDate);
                            });
                    })
                    ->join('dun as dn', function ($join) use ($selectedPRUDate) {
                        $join->on('d.kod_dun', '=', 'dn.kod_dun')
                            ->where('dn.effective_from', '<=', $selectedPRUDate)
                            ->where(function ($q) use ($selectedPRUDate) {
                                $q->whereNull('dn.effective_to')
                                    ->orWhere('dn.effective_to', '>=', $selectedPRUDate);
                            });
                    })
                    ->where('p.pilihan_raya_type', $type)
                    ->where('p.pilihan_raya_series', $series)
                    ->where('dn.parlimen_id', $parlimen)
                    ->where('d.kod_dun', $dun)
                    ->where('l.koddm', $dm);
            }, 'p')
                ->selectRaw("
                        p.kod_lokaliti,
                        p.nama_lokaliti,
                        {$saluranSelectRaw},
                        COUNT(*) AS total
                    ")
                ->groupBy('p.kod_lokaliti', 'p.nama_lokaliti')
                ->orderBy('p.kod_lokaliti')
                ->get();

            if ($rows->isEmpty()) {
                Log::warning('No pengundi found for summary');
                return;
            }

            Log::info('Generating PDF...', [
                'row_count' => $rows->count(),
                'memory_before_pdf_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
            ]);

            $html = view('pengundi.pdf.list_data_pdf', [
                'data' => $rows,
                'areaInfo' => $areaInfo,
                'filters' => $this->filters,
                'saluranList' => $distinctSaluran


            ])->render();

            $mpdf = new Mpdf([
                'format' => 'A4-P',
                'margin_top' => 20,
                'margin_bottom' => 15,
                'margin_left' => 10,
                'margin_right' => 10
            ]);

            // Performance tweaks
            $mpdf->simpleTables = true;
            $mpdf->packTableData = true;
            $mpdf->shrink_tables_to_fit = 1;

            // Render PDF
            $mpdf->WriteHTML($html);

            // Capture output as string
            $pdfContent = $mpdf->Output('', 'S');

            unset($mpdf);
            gc_collect_cycles();
            $now = now()->timestamp;

            $folderPath = "pdfs/{$type}/{$series}/{$dm}";
            $fileName = "pengundi_{$dm}_summary_{$now}.pdf";
            $fullPath = "{$folderPath}/{$fileName}";

            Storage::disk('public')->put($fullPath, $pdfContent);

            unset($rows, $pdfContent);
            gc_collect_cycles();

            Log::info('Summary PDF saved', [
                'path' => $fullPath,
                'execution_time_sec' => round(microtime(true) - $startTime, 2),
                'memory_after_save_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
            ]);

            $user = User::find($this->userId);

            if ($user) {
                $user->notify(new LokalitiPdfGenerated(route('pengundi.list')));
            }

            Log::info('Summary Job COMPLETED SUCCESSFULLY');

        } catch (Throwable $e) {

            Log::error('Summary Job FAILED', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'memory_on_error_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
            ]);

            throw $e;
        }
    }
}