<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Route;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;

    public function __construct($task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title'   => 'New Task Assigned',
            'message' => 'You have been assigned a new task: ' . $this->task->title,
            'url'     => route('task.show', $this->task->id),
            'type'    => 'info',
            'icon'    => 'bi-list-check',
            'task_id' => $this->task->id,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title'   => 'New Task Assigned',
            'message' => 'You have been assigned a new task: ' . $this->task->title,
            'url'     => route('task.show', $this->task->id),
            'type'    => 'info',
            'icon'    => 'bi-list-check',
            'task_id' => $this->task->id,
        ]);
    }
}