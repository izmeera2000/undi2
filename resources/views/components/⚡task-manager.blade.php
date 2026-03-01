<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Task;
use Carbon\Carbon;

new class extends Component {
    public $tasks = [];

    #[On('taskUpdated')]
    public function loadTasks()
    {
        $this->tasks = Task::with(['category', 'assignee'])
            ->orderBy('due_at', 'asc')
            ->get();
    }

    public function mount()
    {
        $this->loadTasks();
    }

    public function toggleTask($id)
    {
        $task = Task::find($id);
        $task->status = $task->status === 'done' ? 'todo' : 'done';
        $task->save();
        $this->loadTasks();
    }

    public function getSectionsProperty()
    {
        $today = Carbon::today();
        $tasks = collect($this->tasks);

        return [
            'Overdue' => $tasks->whereNotNull('due_at')->where('due_at', '<', $today)->where('status', '!=', 'done'),
            'Today' => $tasks->whereNotNull('due_at')->filter(fn($t) => Carbon::parse($t->due_at)->isToday()),
            'Upcoming' => $tasks->filter(fn($t) => !$t->due_at || Carbon::parse($t->due_at)->isFuture()),
        ];
    }

    public function getRelativeTime($date)
    {
        if (!$date)
            return '';
        $date = Carbon::parse($date);
        return $date->isToday() ? 'Today' : ($date->isTomorrow() ? 'Tomorrow' : $date->diffForHumans());
    }
}; ?>

<div class="todo-main">
    <!-- Todo Header -->
    <div class="row align-items-center todo-header mb-3">
        <!-- Button Column -->
        <div class="col-12 col-md-auto mb-2 mb-md-0">
            <button class="btn btn-primary d-block d-md-inline-block w-100 w-md-auto" data-bs-toggle="modal"
                data-bs-target="#addTaskModal">
                <i class="bi bi-plus-lg me-2"></i>Add Task
            </button>
        </div>

        <!-- Title Column -->
        <div class="col d-flex justify-content-center justify-content-md-end">
            <div class="todo-header-title text-center text-md-end">
                <h5>All Tasks</h5>
                <span class="todo-header-count">{{ count($tasks) }} tasks</span>
            </div>
        </div>
    </div>

    <!-- Todo List -->
    <div class="todo-list">
        @foreach($this->sections as $name => $sectionTasks)
            @if($sectionTasks->count() > 0)
                <div class="todo-section" x-data="{ open: true }">
                    <div class="todo-section-header" style="cursor: pointer;">
                        <button class="todo-section-toggle">
                            <i class="bi" :class="open ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                        </button>
                        <h6>{{ $name }}</h6>
                        <span class="todo-section-count">{{ $sectionTasks->count() }}</span>
                    </div>

                    <div class="todo-section-content" x-show="open" x-collapse>
                        @foreach($sectionTasks as $task)
                            <div class="todo-item {{ $task->status === 'done' ? 'completed' : '' }}" data-id="{{ $task->id }}">
                                <div class="todo-item-check">
                                    <input type="checkbox" class="todo-checkbox" id="task{{ $task->id }}"
                                        wire:click.stop="toggleTask({{ $task->id }})" {{ $task->status === 'done' ? 'checked' : '' }}>
                                    <label for="task{{ $task->id }}"></label>
                                </div>

                                <div class="todo-item-content" wire:click="$dispatch('taskSelected', { id: {{ $task->id }} })"
                                    style="cursor: pointer;">
                                    <div class="todo-item-title">{{ $task->title }}</div>
                                    <div class="todo-item-meta">
                                        @if($task->category)
                                            <span class="todo-item-project">{{ $task->category->name }}</span>
                                        @endif
                                        @if($task->due_at)
                                            <span class="todo-item-due">
                                                <i class="bi bi-calendar"></i> {{ $this->getRelativeTime($task->due_at) }}
                                            </span>
                                        @endif
                                        @if($task->assignee)
                                            <span class="todo-item-assignee">Assigned to: {{ $task->assignee->name }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="todo-item-actions">
                                    <span
                                        class="todo-item-priority priority {{ $task->priority ?? 'medium' }}">{{ $task->priority }}</span>
                                </div>

                                <div class="dropdown">
                                    <button class="todo-item-more" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        {{-- 1. View Details: Dispatches to View Modal --}}
                                        <li>
                                            <a class="dropdown-item" href="#"
                                                wire:click.prevent="$dispatch('taskSelected', { id: {{ $task->id }} })">
                                                <i class="bi bi-eye me-2"></i>View Details
                                            </a>
                                        </li>

                                        {{-- 2. Edit: Dispatches to Edit Modal --}}
                                        <li>
                                            <a class="dropdown-item" href="#"
                                                wire:click.prevent="$dispatch('editTask', { id: {{ $task->id }} })">
                                                <i class="bi bi-pencil me-2"></i>Edit
                                            </a>
                                        </li>

                                        {{-- 3. Delete: Calls the PHP method directly with a confirmation --}}
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#"
                                                wire:click.prevent="toggleTask({{ $task->id }})"
                                                wire:confirm="Are you sure you want to delete this task?">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>