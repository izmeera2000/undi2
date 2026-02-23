<!-- View Task Modal -->
<div class="modal fade" id="viewTaskModal" tabindex="-1" aria-labelledby="viewTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewTaskModalLabel">Task Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="todo-view-task">

          <!-- Task Header -->
          <div class="todo-view-header">
            <div class="todo-view-check">
              <input type="checkbox" class="todo-checkbox" id="viewTaskCheckbox">
              <label for="viewTaskCheckbox"></label>
            </div>
            <div class="todo-view-title-wrapper">
              <h4 class="todo-view-title" id="viewTaskTitle">Task Title Here</h4>
            </div>
          </div>

          <!-- Task Meta -->
          <div class="todo-view-meta">
            <div class="todo-view-meta-item">
              <i class="bi bi-folder"></i>
              <span class="todo-view-project" id="viewTaskCategory" style="--project-color: var(--accent-color);">Category</span>
            </div>
            <div class="todo-view-meta-item">
              <i class="bi bi-calendar"></i>
              <span id="viewTaskDueDate">Due Date</span>
            </div>
            <div class="todo-view-meta-item">
              <i class="bi bi-clock"></i>
              <span id="viewTaskDueTime">Due Time</span>
            </div>
            <div class="todo-view-meta-item">
              <i class="bi bi-person"></i>
              <span id="viewTaskAssignee">Assigned to: John Doe</span>
            </div>
            <div class="todo-view-meta-item">
              <i class="bi bi-flag"></i>
              <span class="todo-item-priority" id="viewTaskPriority">Priority</span>
            </div>
          </div>

          <!-- Description -->
          <div class="todo-view-section">
            <h6>Description</h6>
            <p id="viewTaskDescription">Task description goes here.</p>
          </div>

          <!-- Tags -->
          <div class="todo-view-section">
            <h6>Tags</h6>
            <div class="todo-view-tags" id="viewTaskTags"></div>
          </div>

          <!-- Subtasks -->
          <div class="todo-view-section">
            <h6>Subtasks</h6>
            <div class="todo-subtasks" id="viewTaskSubtasks"></div>
          </div>

          <!-- Activity -->
          <div class="todo-view-section" >
            <h6>Activity</h6>
            <div class="todo-activity" id="viewTaskActivity" style="max-height: 200px;overflow:scroll"></div>
          </div>

        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
<button type="button"
        class="btn btn-outline-danger me-auto d-none"
        id="viewTaskDelete">
  <i class="bi bi-trash me-1"></i> Delete
</button>
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="viewTaskEdit">
          <i class="bi bi-pencil me-1"></i>Edit
        </button>
      </div>
    </div>
  </div>
</div>
