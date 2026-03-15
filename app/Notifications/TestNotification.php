<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $message;
    protected string $title;
    protected string $url;

    public function __construct(string $title = "Test Notification", string $message = "This is a test notification", string $url = "#")
    {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
    }

    // Channels: database + broadcast
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    // Database storage
    public function toDatabase($notifiable)
    {
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'url'     => $this->url,
            'notify_type'    => 'info', // can be success, warning, etc.
            'icon'    => 'bi-bell', // bootstrap icon
        ];
    }

    // Broadcast for Laravel Echo
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id'      => $this->id,
            'title'   => $this->title,
            'message' => $this->message,
            'url'     => $this->url,
            'notify_type'    => 'info',
            'icon'    => 'bi-bell',
        ]);
    }
}