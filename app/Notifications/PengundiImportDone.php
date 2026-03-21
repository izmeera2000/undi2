<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Route;

class PengundiImportDone extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $url;
 
    public function __construct(string $url)
    {
        $this->url = $url;
     }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Pengundi Import Ready',
            'message' => 'Your Pengundi Import has been completed.',
            'url' => $this->url,
            'notify_type' => 'success',
            'icon' => 'bi-file-earmark-pdf',
         ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'title' => 'Pengundi Import Ready',
            'message' => 'Your Pengundi Import has been completed.',
            'url' => $this->url,
            'notify_type' => 'success',
            'icon' => 'bi-file-earmark-pdf',
          ]);
    }
}