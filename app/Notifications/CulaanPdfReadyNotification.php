<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Route;

class CulaanPdfReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $pdfUrl;
    protected int $culaanId;

    public function __construct(string $pdfUrl, int $culaanId)
    {
        $this->pdfUrl = $pdfUrl;
        $this->culaanId = $culaanId;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Culaan PDF Ready',
            'message' => 'Your Culaan report has been generated.',
            'url' => $this->pdfUrl,
            'notify_type' => 'success',
            'icon' => 'bi-file-earmark-pdf',
            'culaan_id' => $this->culaanId,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'title' => 'Culaan PDF Ready',
            'message' => 'Your Culaan report has been generated.',
            'url' => $this->pdfUrl,
            'notify_type' => 'success',
            'icon' => 'bi-file-earmark-pdf',
            'culaan_id' => $this->culaanId,

        ]);
    }
}