<?php

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskCategory;
use Livewire\Attributes\Computed;

new class extends Component {
    // Form Properties
    public $title = '';
    public $description = '';
    public $due_date = '';
    public $due_time = '';
    public $assigned_to = '';
    public $priority = 'medium';
    public $category_id = '';
    public $tags = '';

    // Subtasks logic
    public $subtasks = [];

    // Data for dropdowns
    public function with()
    {
        return [
            'users' => User::where('id', '!=', auth()->id())->get(),
            'categories' => TaskCategory::all(),
        ];
    }

    #[On('projectAdded')] // Auto-refresh categories if a new project is created
    public function refreshCategories()
    {
        $this->render();
    }

    public function addSubtask()
    {
        $this->subtasks[] = '';
    }

    public function removeSubtask($index)
    {
        unset($this->subtasks[$index]);
        $this->subtasks = array_values($this->subtasks);
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|min:3',
            'category_id' => 'required',
        ]);

        $task = Task::create([
            'title' => $this->title,
            'description' => $this->description,
            'due_at' => $this->due_date ? ($this->due_date . ' ' . ($this->due_time ?: '00:00:00')) : null,
            'assigned_to' => $this->assigned_to ?: auth()->id(),
            'priority' => $this->priority,
            'category_id' => $this->category_id,
            'created_by' => auth()->id(),
            'status' => 'todo',
        ]);

        // Save subtasks if your model has the relationship
        foreach ($this->subtasks as $subtaskTitle) {
            if (!empty($subtaskTitle)) {
                $task->subtasks()->create(['title' => $subtaskTitle]);
            }
        }

        $this->reset();
        $this->js("$('#addTaskModal').modal('hide')");
        $this->dispatch('taskUpdated'); // Refresh the Task List component
    }
}; ?>

<div wire:ignore.self class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Task Title</label>
                    <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea wire:model="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Due Date</label>
                        <input type="date" wire:model="due_date" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Due Time</label>
                        <input type="time" wire:model="due_time" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6  @cannot('task.add.others') d-none @endcannot">
                        <label class="form-label">Assigned To</label>
                        <select wire:model="assigned_to" class="form-select">
                            <option value="">Select user</option>
                            {{-- MUST USE $this->users --}}
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Priority</label>
                        <select wire:model="priority" class="form-select">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror">
                        <option value="">Select category</option>
                        {{-- MUST USE $this->categories --}}
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subtasks</label>
                    @foreach($subtasks as $index => $subtask)
                        <div class="input-group mb-2" wire:key="subtask-{{ $index }}">
                            <input type="text" wire:model="subtasks.{{ $index }}" class="form-control form-control-sm"
                                placeholder="Subtask title">
                            <button class="btn btn-outline-danger btn-sm" wire:click="removeSubtask({{ $index }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    @endforeach
                    <button type="button" wire:click="addSubtask" class="btn btn-sm btn-outline-secondary mt-1">
                        + Add Subtask
                    </button>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">Add Task</span>
                    <span wire:loading wire:target="save" class="spinner-border spinner-border-sm"></span>
                </button>
            </div>
        </div>
    </div>
</div>