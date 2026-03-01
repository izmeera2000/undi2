<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskCategory;
use Carbon\Carbon;

new class extends Component {
    public ?Task $task = null;

    // Properties for wire:model
    public $title, $description, $due_date, $due_time, $category_id, $priority, $assigned_to, $tags;
    public $subtasks = [];

    public function with()
    {
        return [
            'users' => User::all(),
            'categories' => TaskCategory::all(),
        ];
    }

    #[On('editTask')]
    public function loadTask($id)
    {
         $this->task = Task::with('subtasks')->find($id);

        // REPLACES: editTask(taskId) jQuery setters
        $this->title = $this->task->title;
        $this->description = $this->task->description;
        $this->category_id = $this->task->category_id;
        $this->priority = $this->task->priority ?? 'medium';
        $this->assigned_to = $this->task->assigned_to;
        $this->tags = is_array($this->task->tags) ? implode(', ', $this->task->tags) : '';

        // REPLACES: task.subtasks?.forEach logic
        // Ensure each subtask has 'title' and 'is_completed' keys
        $this->subtasks = collect($this->task->subtasks ?? [])->map(function ($st) {
            return [
                'id' => $st->id, // <--- CRITICAL: This prevents duplication
                'title' => $st->title ?? '',
                'is_completed' => (bool) ($st->is_completed ?? false)
            ];
        })->toArray();

        if ($this->task->due_at) {
            $dt = Carbon::parse($this->task->due_at);
            $this->due_date = $dt->format('Y-m-d');
            $this->due_time = $dt->format('H:i');
        }

           $this->js("
        $('#viewTaskModal').modal('hide');
        
        // Use a slight delay so Bootstrap doesn't glitch the backdrop
        setTimeout(() => {
            $('#editTaskModal').modal('show');
        }, 150);
    ");
     }

    public function addSubtask()
    {
        // REPLACES: $subtasks.append(...)
        $this->subtasks[] = ['title' => '', 'is_completed' => false];
    }

    public function removeSubtask($index)
    {
        $stData = $this->subtasks[$index];

        // If it exists in DB, delete it now
        if (isset($stData['id'])) {
            $this->task->subtasks()->where('id', $stData['id'])->delete();
        }

        unset($this->subtasks[$index]);
        $this->subtasks = array_values($this->subtasks);
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|min:3',
        ]);

        // 1. Update the Main Task
        $this->task->update([
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'priority' => $this->priority,
            'assigned_to' => $this->assigned_to,
            'due_at' => $this->due_date ? Carbon::parse($this->due_date . ' ' . ($this->due_time ?: '00:00:00')) : null,
            // Tags logic
            'tags' => collect(explode(',', $this->tags))->map(fn($t) => trim($t))->filter()->values()->toArray(),
        ]);

        // 2. Handle Subtasks (Separate Table Logic)
        foreach ($this->subtasks as $stData) {
            if (!empty($stData['title'])) {
                if (isset($stData['id'])) {
                    // Update existing subtask
                    $this->task->subtasks()->where('id', $stData['id'])->update([
                        'title' => $stData['title'],
                        'is_completed' => $stData['is_completed'] ?? false,
                    ]);
                } else {
                    // Create new subtask
                    $this->task->subtasks()->create([
                        'title' => $stData['title'],
                        'is_completed' => $stData['is_completed'] ?? false,
                    ]);
                }
            }
        }

        $this->js("$('#editTaskModal').modal('hide')");
        $this->dispatch('taskUpdated');
    }

}; ?>

<div wire:ignore.self class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Task Title --}}
                <div class="mb-3">
                    <label class="form-label">Task Title</label>
                    <input type="text" class="form-control" wire:model="title">
                </div>

                {{-- Description --}}
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="4" wire:model="description"></textarea>
                </div>

                {{-- Due Date/Time --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" wire:model="due_date">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Due Time</label>
                        <input type="time" class="form-control" wire:model="due_time">
                    </div>
                </div>

                {{-- Subtasks Section --}}
                <div class="mb-3">
                    <label class="form-label font-weight-bold">Subtasks</label>
                    <div class="todo-edit-subtasks">
                        @foreach($subtasks as $index => $st)
                            <div class="input-group mb-2" wire:key="edit-subtask-{{ $index }}">
                                <div class="input-group-text">
                                    {{-- REPLACES: !!$(this).find('input[type=checkbox]').prop('checked') --}}
                                    <input type="checkbox" class="form-check-input mt-0"
                                        wire:model="subtasks.{{ $index }}.is_completed">
                                </div>

                                {{-- IMPORTANT: No 'value' attribute here. wire:model handles it --}}
                                <input type="text" class="form-control" wire:model="subtasks.{{ $index }}.title"
                                    placeholder="Enter subtask...">

                                <button type="button" class="btn btn-outline-danger"
                                    wire:click="removeSubtask({{ $index }})">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" wire:click="addSubtask">
                        <i class="bi bi-plus me-1"></i>Add Subtask
                    </button>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" wire:click="save">
                    <i class="bi bi-check-lg me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>