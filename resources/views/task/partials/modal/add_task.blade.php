<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="addTaskForm">
                @csrf

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Task Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" id="addTaskDueDate" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Due Time</label>
                            <input type="time" id="addTaskDueTime" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6 {{ auth()->user()->can('task.assign.others') ? 'd-none' : '' }}">
                            <label class="form-label">Assigned To</label>
                            <select name="assigned_to" id="addTaskAssignee" class="form-select">
                                <option value="">Select user</option>
                                @can('task.add.others')

                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                @endcan

                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Priority</label>
                            <select name="priority" id="addTaskPriority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>

                    <div class=" mb-3">
                        <label class="form-label">Category</label>
                        <select id="addTaskCategory" name="category_id" class="form-control">
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ isset($task) && $task->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tags</label>
                        <input type="text" id="addTaskTags" class="form-control" placeholder="tag1, tag2, tag3">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subtasks</label>
                        <div id="addTaskSubtasks"></div>
                        <button type="button" id="addSubtaskBtnNew" class="btn btn-sm btn-outline-secondary mt-2">
                            + Add Subtask
                        </button>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="createTaskBtn">
                        Add Task
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>