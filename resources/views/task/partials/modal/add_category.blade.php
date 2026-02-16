   

      <!-- Add Project Modal -->
      <div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addProjectModalLabel">Add Project</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form>
                <div class="mb-3">
                  <label class="form-label">Project Name</label>
                  <input type="text" class="form-control" placeholder="Enter project name">
                </div>
                <div class="mb-3">
                  <label class="form-label">Color</label>
                  <div class="todo-color-picker">
                    <label class="todo-color-option">
                      <input type="radio" name="projectColor" value="primary" checked>
                      <span style="background: var(--accent-color);"></span>
                    </label>
                    <label class="todo-color-option">
                      <input type="radio" name="projectColor" value="success">
                      <span style="background: var(--success-color);"></span>
                    </label>
                    <label class="todo-color-option">
                      <input type="radio" name="projectColor" value="warning">
                      <span style="background: var(--warning-color);"></span>
                    </label>
                    <label class="todo-color-option">
                      <input type="radio" name="projectColor" value="danger">
                      <span style="background: var(--danger-color);"></span>
                    </label>
                    <label class="todo-color-option">
                      <input type="radio" name="projectColor" value="info">
                      <span style="background: var(--info-color);"></span>
                    </label>
                    <label class="todo-color-option">
                      <input type="radio" name="projectColor" value="secondary">
                      <span style="background: var(--muted-color);"></span>
                    </label>
                  </div>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary">Add Project</button>
            </div>
          </div>
        </div>
      </div>

