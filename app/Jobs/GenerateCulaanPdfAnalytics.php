<?php

namespace App\Jobs;

use Mpdf\Mpdf;
use App\Models\Culaan;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;


use App\Models\User;
use App\Notifications\CulaanAnalyticsPdfReadyNotification;


class GenerateCulaanPdfAnalytics implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $charts;
    protected $culaanId;
    protected $userId;


    protected $filters;

    public function __construct($charts, $culaanId, $userId, $filters = [])
    {
        $this->charts = $charts;
        $this->culaanId = $culaanId;
        $this->userId = $userId;
        $this->filters = $filters;
    }

    public function handle(): void
    {
        $culaan = Culaan::with('election')->find($this->culaanId);

        Log::info('Culaan fetched for PDF', [
            'culaan' => $culaan,
            'election' => $culaan?->election
        ]);

        $logo = base64_encode(file_get_contents(public_path('assets/img/UMNO_logo.png')));

        $html = View::make('culaan.analytics_pdf', [
            'charts' => $this->charts,
            'culaan' => $culaan,
        ])->render();

        $mpdf = new Mpdf([
            'format' => 'A4-P',
            'margin_top' => 45,
            'margin_bottom' => 20,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $header = view('culaan.analytics_pdf_header', [
            'logo' => $logo,
            'culaan' => $culaan,
            'generatedAt' => now('Asia/Kuala_Lumpur')->format('d M Y H:i'),
        ])->render();

        $mpdf->SetHTMLHeader($header);
        $mpdf->WriteHTML($html);

        $now = now()->timestamp;

        $sanitize = fn($value) =>
            $value ? preg_replace('/[^A-Za-z0-9]/', '_', $value) : 'all';

        $lokaliti = $sanitize($this->filters['lokaliti'] ?? null);
        $statusLabel = $statuses[$this->filters['status_culaan'] ?? ''] ?? 'all';

        $fileName = "culaan_analytics_{$this->culaanId}_lokaliti_{$lokaliti}_status_{$statusLabel}_{$now}.pdf";

        $folderPath = storage_path("app/public/pdfs/culaan/{$this->culaanId}");

        // --- Delete previous files for same culaan/lokaliti/status ---
        if (file_exists($folderPath)) {
            foreach (glob("{$folderPath}/culaan_analytics_{$this->culaanId}_lokaliti_{$lokaliti}_status_{$statusLabel}_*.pdf") as $oldFile) {
                @unlink($oldFile); // suppress errors if file cannot be deleted
            }
        } else {
            mkdir($folderPath, 0777, true);
        }

        $mergedPath = "{$folderPath}/{$fileName}";

        // Save PDF
        $mpdf->Output($mergedPath, 'F');

        $user = User::find($this->userId);
        $relativePath = "pdfs/culaan/{$this->culaanId}/{$fileName}";

        if ($user) {
            $url = asset("storage/{$relativePath}");
            $user->notify(new CulaanAnalyticsPdfReadyNotification($url, $this->culaanId));
        }
    }
}