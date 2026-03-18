<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Route;

class CulaanImportDone extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $url;
    protected int $culaanId;

    public function __construct(string $url, int $culaanId)
    {
        $this->url = $url;
        $this->culaanId = $culaanId;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Culaan Import Ready',
            'message' => 'Your Culaan Import has been completed.',
            'url' => $this->url,
            'notify_type' => 'success',
            'icon' => 'bi-file-earmark-pdf',
            'culaan_id' => $this->culaanId,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'title' => 'Culaan Import Ready',
            'message' => 'Your Culaan Import has been completed.',
            'url' => $this->url,
            'notify_type' => 'success',
            'icon' => 'bi-file-earmark-pdf',
            'culaan_id' => $this->culaanId,

        ]);
    }
}