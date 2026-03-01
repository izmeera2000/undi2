<?php

use Livewire\Component;
use App\Models\Task;
use Carbon\Carbon;

new class extends Component {
    public $tasks = [];

    public function mount()
    {
        $this->loadTasks();
    }

    public function loadTasks()
    {
        $this->tasks = Task::with(['category', 'assignee'])
            ->where('status', 'todo')
            ->orderBy('due_at')
            ->get();
    }

    public function relativeTime($date)
    {
        $date = Carbon::parse($date);
        if ($date->isToday())
            return 'Today';
        if ($date->isTomorrow())
            return 'Tomorrow';
        return $date->diffForHumans();
    }

}; 
?>

@placeholder

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom-0 pt-3">
        <div class="skeleton-shimmer" style="width: 80px; height: 20px; border-radius: 4px;"></div>
    </div>
    <div class="card-body">
        <!-- Repeat 3 skeleton rows -->
        @for ($i = 0; $i < 3; $i++)
            <div class="d-flex align-items-center mb-3">
                <div class="flex-grow-1">
                    <div class="skeleton-shimmer mb-2" style="width: 60%; height: 18px; border-radius: 4px;"></div>
                    <div class="d-flex gap-2">
                        <div class="skeleton-shimmer" style="width: 20%; height: 12px; border-radius: 4px;"></div>
                        <div class="skeleton-shimmer" style="width: 15%; height: 12px; border-radius: 4px;"></div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
    <style>
        .skeleton-shimmer {
            background: #f6f7f8;
            background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-repeat: no-repeat;
            background-size: 800px 104px;
            animation: shimmer 1.5s infinite linear;
        }

        @keyframes shimmer {
            0% {
                background-position: -468px 0;
            }

            100% {
                background-position: 468px 0;
            }
        }
    </style>
</div>
@endplaceholder

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center pt-3">
        <h5 class="card-title mb-0" style="font-weight: 600;">Tasks</h5>
        <div class="card-actions">
            <a href="{{ route('task.index') }}" class="btn btn-sm btn-light text-primary fw-bold">View All</a>
        </div>
    </div>
    <div class="card-body">
        @if(count($tasks) === 0)
            <div class="text-center py-5">
                <i class="bi bi-check2-circle text-success" style="font-size: 40px; opacity: .4;"></i>
                <h6 class="mt-2">No pending tasks 🎉</h6>
                <p class="text-muted small">You're all caught up!</p>
            </div>
        @else
            @foreach($tasks as $task)
                <div class="todo-item mb-2 p-2 rounded-3 border-start border-4 border-primary bg-light bg-opacity-50"
                    wire:click="$dispatch('taskSelected', { id: {{ $task->id }} })" style="cursor: pointer; transition: 0.2s;">
                    <div class="todo-item-content">
                        <div class="todo-item-title fw-bold text-dark">{{ $task->title }}</div>
                        <div class="todo-item-meta small text-muted mt-1">
                            @if($task->category)
                                <span class="badge bg-white text-dark border me-1">{{ $task->category->name }}</span>
                            @endif
                            @if($task->due_at)
                                <span class="me-2"><i
                                        class="bi bi-calendar-event me-1"></i>{{ $this->relativeTime($task->due_at) }}</span>
                            @endif
                            @if($task->assignee)
                                <span><i class="bi bi-person me-1"></i>{{ $task->assignee->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>