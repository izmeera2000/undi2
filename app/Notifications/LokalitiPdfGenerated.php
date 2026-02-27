<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class LokalitiPdfGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'PDF Generation Completed',
            'message' => 'Your PDF is ready.',
            'file' => $this->filePath
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'PDF Generation Completed',
            'message' => 'Your PDF is ready.',
            'file' => $this->filePath
        ]);
    }
}