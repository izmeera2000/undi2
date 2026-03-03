<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Task;
use App\Models\Subtask;
use Carbon\Carbon;

new class extends Component {
    public ?Task $task = null;

    #[On('taskSelected')]
    public function loadTask($id)
    {
        // Eager load everything needed for the JS logic replacement
        $this->task = Task::with(['category', 'assignee', 'subtasks', 'activities.causer.profile', 'creator'])
            ->find($id);

        if ($this->task) {
            $this->js("$('#viewTaskModal').modal('show')");
        }
    }

    public function toggleComplete()
    {
        $this->task->status = $this->task->status === 'done' ? 'todo' : 'done';
        $this->task->save();
        $this->dispatch('taskUpdated');
    }

    public function deleteTask()
    {
        $this->task->delete();
        $this->js("$('#viewTaskModal').modal('hide')");
        $this->dispatch('taskUpdated');
    }


    public function toggleSubtask($subtaskId) {
        $subtask = Subtask::find($subtaskId);
        if ($subtask) {
            $subtask->update(['is_completed' => !$subtask->is_completed]);
            
            // Refresh local task to show checkmark immediately
            $this->task->load('subtasks'); 
            
            // REPLACES: renderTasks(tasks) - Updates the background list
            $this->dispatch('taskUpdated'); 
        }
    }

}; ?>

<div wire:ignore.self class="modal fade" id="viewTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            @if($task)
            <div class="modal-header">
                <h5 class="modal-title">Task Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="todo-view-task">
                    <!-- Task Header -->
                    <div class="todo-view-header">
                        <div class="todo-view-check">
                            <input type="checkbox" class="todo-checkbox" id="viewTaskCheckbox" 
                                   wire:click="toggleComplete" {{ $task->status === 'done' ? 'checked' : '' }}>
                            <label for="viewTaskCheckbox"></label>
                        </div>
                        <div class="todo-view-title-wrapper">
                            <h4 class="todo-view-title {{ $task->status === 'done' ? 'text-decoration-line-through text-muted' : '' }}">
                                {{ $task->title }}
                            </h4>
                        </div>
                    </div>

                    <!-- Task Meta -->
                    <div class="todo-view-meta">
                        <div class="todo-view-meta-item">
                            <i class="bi bi-folder"></i>
                            <span class="todo-view-project">{{ $task->category->name ?? '' }}</span>
                        </div>
                        <div class="todo-view-meta-item">
                            <i class="bi bi-calendar"></i>
                            <span>{{ $task->due_at ? Carbon::parse($task->due_at)->format('l, F d, Y') : 'No Date' }}</span>
                        </div>
                        <div class="todo-view-meta-item">
                            <i class="bi bi-clock"></i>
                            <span>{{ $task->due_at ? Carbon::parse($task->due_at)->format('h:i A') : '' }}</span>
                        </div>
                        <div class="todo-view-meta-item">
                            <i class="bi bi-person"></i>
                            <span>Assigned to: {{ $task->assignee->name ?? 'Unassigned' }}</span>
                        </div>
                        <div class="todo-view-meta-item">
                            <i class="bi bi-flag"></i>
                            <span class="todo-item-priority {{ $task->priority }}">{{ ucfirst($task->priority ?? 'N/A') }}</span>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="todo-view-section">
                        <h6>Description</h6>
                        <p>{{ $task->description ?? 'No Description' }}</p>
                    </div>

                    <!-- Subtasks (Matches your map logic) -->
                    <div class="todo-view-section">
                        <h6>Subtasks</h6>
                         <div class="todo-subtasks">
                        @forelse($task->subtasks as $st)
                            <div class="todo-subtask d-flex align-items-center mb-1">
                                {{-- wire:change handles the AJAX toggle --}}
                                <input type="checkbox" 
                                       class="todo-subtask-check form-check-input me-2" 
                                       id="st-{{ $st->id }}" 
                                       wire:change="toggleSubtask({{ $st->id }})" 
                                       {{ $st->is_completed ? 'checked' : '' }}>
                                <label for="st-{{ $st->id }}" class="{{ $st->is_completed ? 'text-decoration-line-through' : '' }}">
                                    {{ $st->title }}
                                </label>
                            </div>
                        @empty
                            <p class="text-muted small">No Subtasks</p>
                        @endforelse
                    </div>
                    </div>

                    <!-- Activity (Matches your map logic) -->
                    <div class="todo-view-section">
                        <h6>Activity</h6>
                        <div class="todo-activity" style="max-height: 200px; overflow-y: auto;">
                            @forelse($task->activities as $act)
                                <div class="todo-activity-item">
                                    <img src="{{ $act->causer?->profile?->profile_picture ?? asset('assets/img/avatars/avatar-placeholder.webp') }}" alt="User">
                                    <div class="todo-activity-content">
                                        <span class="todo-activity-text"><strong>{{ $act->causer->name ?? 'Unknown' }}</strong> {{ $act->description }}</span>
                                        <span class="todo-activity-time">{{ $act->created_at->format('d/m/Y, h:i A') }}</span>
                                    </div>
                                </div>
                            @empty
                                <p>No Activity</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer with Permissions Check -->
            <div class="modal-footer">
                @if(auth()->user()->can('task.delete.others') || (auth()->user()->can('task.delete') && $task->creator_id === auth()->id()))
                    <button type="button" class="btn btn-outline-danger me-auto" wire:click="deleteTask" wire:confirm="Are you sure?">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                @endif
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
<button type="button" class="btn btn-primary" wire:click="$dispatch('editTask', { id: {{ $task->id }} })">
    <i class="bi bi-pencil me-1"></i>Edit
</button>
            </div>
            @endif
        </div>
    </div>
</div>
