<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form class="todo-form" id="editTaskForm">
          <div class="mb-3">
            <label class="form-label">Task Title</label>
            <input type="text" class="form-control" id="editTaskTitle">
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" rows="4" id="editTaskDescription"></textarea>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Due Date</label>
              <input type="date" class="form-control" id="editTaskDueDate">
            </div>
            <div class="col-md-6">
              <label class="form-label">Due Time</label>
              <input type="time" class="form-control" id="editTaskDueTime">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Category</label>
              <select id="editTaskProject" name="category_id" class="form-control">
                <option value="">Select category</option>
                @foreach ($categories as $category)
                  <option value="{{ $category->id }}" {{ isset($task) && $task->category_id == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                  </option>
                @endforeach
              </select>

            </div>
            <div class="col-md-6">
              <label class="form-label">Priority</label>
              <select class="form-select" id="editTaskPriority">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Assigned To</label>
            <select id="editTaskAssignee" name="assigned_to" class="form-control">
              <option value="">Select assignee</option>
              @foreach ($users as $user)
                <option value="{{ $user->id }}" {{ isset($task) && $task->assigned_to == $user->id ? 'selected' : '' }}>
                  {{ $user->name }}
                </option>
              @endforeach
            </select>

          </div>
          <div class="mb-3">
            <label class="form-label">Tags</label>
            <input type="text" class="form-control" id="editTaskTags">
            <div class="form-text">Separate tags with commas</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Subtasks</label>
            <div class="todo-edit-subtasks" id="editTaskSubtasks">
              <!-- Dynamically populated -->
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="addSubtaskBtn">
              <i class="bi bi-plus me-1"></i>Add Subtask
            </button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger me-auto" id="editTaskDelete">
          <i class="bi bi-trash me-1"></i>Delete
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveTaskBtn">
          <i class="bi bi-check-lg me-1"></i>Save Changes
        </button>
      </div>
    </div>
  </div>
</div>