<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class LokalitiPdfGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $url;

    public function __construct($url)
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
            'title'   => 'PDF Generation Completed',
            'message' => 'Your PDF is ready. Click to view.',
            'url'     => $this->url, // ✅ Changed from file → url
            'type'    => 'success',
            'icon'    => 'bi-file-earmark-pdf',
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title'   => 'PDF Generation Completed',
            'message' => 'Your PDF is ready. Click to view.',
            'url'     => $this->url, // ✅ Changed from file → url
            'type'    => 'success',
            'icon'    => 'bi-file-earmark-pdf',
        ]);
    }
}