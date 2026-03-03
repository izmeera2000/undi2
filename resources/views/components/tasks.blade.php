<?php

use Livewire\Component;
use App\Models\Task;
use Carbon\Carbon;

new class extends Component {
    public $tasks = [];
    public $loadFailed = false;

    public function mount()
    {
        $this->loadTasks();
    }

    public function loadTasks()
    {
        try {
            // Using toArray() ensures the Snapshot is lightweight and 
            // prevents "Snapshot Missing" errors caused by heavy Eloquent objects.
            $this->tasks = Task::with(['category', 'assignee'])
                ->where('status', 'todo')
                ->orderBy('due_at')
                ->take(5)
                ->get()
                ->toArray(); 

            $this->loadFailed = false;
        } catch (\Exception $e) {
            logger()->error("Task Widget Error: " . $e->getMessage());
            $this->loadFailed = true;
        }
    }

    public function relativeTime($date)
    {
        if (!$date) return 'No date';
        
        $date = Carbon::parse($date);
        if ($date->isToday()) return 'Today';
        if ($date->isTomorrow()) return 'Tomorrow';
        
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
        @if($loadFailed)
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 40px; opacity: .4;"></i>
                <h6 class="mt-2">Failed to load tasks</h6>
                <button wire:click="loadTasks" class="btn btn-sm btn-outline-primary mt-2">Retry</button>
            </div>
        @elseif(count($tasks) === 0)
            <div class="text-center py-5">
                <i class="bi bi-check2-circle text-success" style="font-size: 40px; opacity: .4;"></i>
                <h6 class="mt-2">No pending tasks 🎉</h6>
                <p class="text-muted small">You're all caught up!</p>
            </div>
        @else
            @foreach($tasks as $task)
                {{-- wire:key prevents the "Snapshot Missing" error during re-renders --}}
                <div wire:key="task-{{ $task['id'] }}" 
                    class="todo-item mb-2 p-2 rounded-3 border-start border-4 border-primary bg-light bg-opacity-50"
                    wire:click="$dispatch('taskSelected', { id: {{ $task['id'] }} })" 
                    style="cursor: pointer; transition: 0.2s;"
                    onmouseover="this.style.backgroundColor='#e9ecef'" 
                    onmouseout="this.style.backgroundColor='rgba(248, 249, 250, 0.5)'">
                    
                    <div class="todo-item-content">
                        <div class="todo-item-title fw-bold text-dark">{{ $task['title'] }}</div>
                        <div class="todo-item-meta small text-muted mt-1 d-flex flex-wrap align-items-center gap-2">
                            
                            @if(!empty($task['category']))
                                <span class="badge bg-white text-dark border">{{ $task['category']['name'] }}</span>
                            @endif
                            
                            @if($task['due_at'])
                                @php $isPast = Carbon::parse($task['due_at'])->isPast(); @endphp
                                <span class="{{ $isPast ? 'text-danger fw-bold' : '' }}">
                                    <i class="bi bi-calendar-event me-1"></i>{{ $this->relativeTime($task['due_at']) }}
                                </span>
                            @endif
                            
                            @if(!empty($task['assignee']))
                                <span><i class="bi bi-person me-1"></i>{{ $task['assignee']['name'] }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>