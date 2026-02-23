@extends('layouts.app')

@section('title', 'Task')



@section('breadcrumb')
  @php
    // Build dynamic crumbs based on request
    $crumbs = [
      ['label' => 'Task', 'url' => route('task.index')],
      ['label' => 'List', 'url' => route('task.index')],
    ];

  @endphp

@endsection


@push('styles')


@endpush


@section('content')


  <section class="section">


    <div class="todo-container">



      <!-- Todo Main -->
      <div class="todo-main" id="todoMain">
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
              <span class="todo-header-count" id="taskCount">24 tasks</span>
            </div>
          </div>

        </div>



        <!-- Todo List -->
        <div class="todo-list" id="taskList">


        </div>



        {{-- <!-- Quick Add Task -->
        <div class="todo-quick-add">
          <button class="todo-quick-add-btn" id="quickAddBtn">
            <i class="bi bi-plus-lg"></i>
            <span>Add a task</span>
          </button>
          <div class="todo-quick-add-form" id="quickAddForm" style="display: none;">
            <input type="text" class="form-control" id="quickTaskInput" placeholder="What do you need to do?">
            <div class="todo-quick-add-actions">
              <button class="btn btn-sm btn-outline-secondary" title="Set due date">
                <i class="bi bi-calendar"></i>
              </button>
              <button class="btn btn-sm btn-outline-secondary" title="Set priority">
                <i class="bi bi-flag"></i>
              </button>
              <button class="btn btn-sm btn-outline-secondary" title="Add to project">
                <i class="bi bi-folder"></i>
              </button>
              <div class="ms-auto">
                <button class="btn btn-sm btn-outline-secondary me-1" id="cancelQuickAdd">Cancel</button>
                <button class="btn btn-sm btn-primary" id="saveQuickAdd">Add Task</button>
              </div>
            </div>
          </div>
        </div> --}}
      </div>
    </div>



    @include('task.partials.modal.add_category')
    @include('task.partials.modal.add_task')
    @include('task.partials.modal.edit_task')
    @include('task.partials.modal.view_task')



  </section>


@endsection

@push('scripts')


  <script>

    let tasks = [];
    let users = [];
    let categories = [];

    $(document).ready(function () {

      // Setup CSRF token for all AJAX
      const token = $('meta[name="csrf-token"]').attr('content');

      if (token) {
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': token
          }
        });
      } else {
        console.error('CSRF token not found.');
      }

      // Initial load
      getTasks();
    });


    // --------------------------------------
    // Utility
    // --------------------------------------
    function getRelativeTime(date) {
      const now = new Date();
      const diff = date - now;
      const days = Math.floor(diff / (1000 * 60 * 60 * 24));
      if (days === 0) return 'Today';
      if (days === 1) return 'Tomorrow';
      if (days > 1) return `${days} days`;
      if (days === -1) return 'Yesterday';
      return `${Math.abs(days)} days ago`;
    }


    // --------------------------------------
    // Task CRUD / Actions
    // --------------------------------------

    // Get all tasks
    function getTasks() {
      $.post('{{ route("task.data") }}')
        .done(function (data) {
          tasks = data;
          console.log(tasks);
          renderTasks(tasks);
        })
        .fail(function (xhr) {

          if (xhr.status === 401) {
            window.location.href = "{{ route('login') }}";
          }
          else if (xhr.status === 419) {
            location.reload();
          }

        });
    }


    // Render tasks in sections
    function renderTasks(tasks) {
      const today = new Date().toISOString().split('T')[0];
      const sections = { Today: [], Upcoming: [], Overdue: [] };

      tasks.forEach(task => {
        if (!task.due_at) sections.Upcoming.push(task);
        else if (task.due_at.split('T')[0] === today) sections.Today.push(task);
        else if (task.due_at.split('T')[0] > today) sections.Upcoming.push(task);
        else sections.Overdue.push(task);
      });


      const totalTasks = sections.Today.length + sections.Upcoming.length + sections.Overdue.length;
      $('#taskCount').text(`${totalTasks} ${totalTasks === 1 ? 'task' : 'tasks'}`);



      let html = '';

      Object.keys(sections).forEach(sectionName => {
        const sectionTasks = sections[sectionName];
        if (!sectionTasks.length) return;

        html += `<div class="todo-section">
                                                            <div class="todo-section-header">
                                                              <button class="todo-section-toggle"><i class="bi bi-chevron-down"></i></button>
                                                              <h6>${sectionName}</h6>
                                                              <span class="todo-section-count">${sectionTasks.length}</span>
                                                            </div>
                                                            <div class="todo-section-content">`;

        sectionTasks.forEach(task => {
          html += renderTaskItem(task);
        });

        html += `</div></div>`;
      });

      $('#taskList').html(html);
    }

    // Render a single task HTML
    function renderTaskItem(task) {
      const checked = task.status === 'done' ? 'checked' : '';
      const tagsHtml = Array.isArray(task.tags) && task.tags.length
        ? task.tags.map(tag => `<span class="todo-item-tag">${tag}</span>`).join(' ')
        : '';
      const relativeTime = task.due_at ? getRelativeTime(new Date(task.due_at)) : '';

      return `<div class="todo-item" data-id="${task.id}">
                                                            <div class="todo-item-check">
                                                              <input type="checkbox" class="todo-checkbox" id="task${task.id}"  data-id="${task.id}" ${checked}>
                                                              <label for="task${task.id}"></label>
                                                            </div>

                                                            <div class="todo-item-content">
                                                              <div class="todo-item-title">${task.title}</div>
                                                              <div class="todo-item-meta">
                                                                ${task.category ? `<span class="todo-item-project">${task.category.name}</span>` : ''}
                                                                ${relativeTime ? `<span class="todo-item-due"><i class="bi bi-calendar"></i> ${relativeTime}</span>` : ''}
                                                                ${tagsHtml}
                                                                ${task.assignee ? `<span class="todo-item-assignee">Assigned to: ${task.assignee.name}</span>` : ''}
                                                              </div>
                                                            </div>

                                                            <div class="todo-item-actions">
                                                              <span class="todo-item-priority ${task.priority}">${task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}</span>
                                                              <div class="dropdown">
                                                                <button class="todo-item-more" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                  <li><a class="dropdown-item todo-view-btn-trigger" href="#" data-id="${task.id}"><i class="bi bi-eye me-2"></i>View Details</a></li>
                                                                  <li><a class="dropdown-item todo-edit-btn-trigger" href="#" data-id="${task.id}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                                                  <li><a class="dropdown-item todo-delete-btn-trigger text-danger" href="#" data-id="${task.id}"><i class="bi bi-trash me-2"></i>Delete</a></li>
                                                                </ul>
                                                              </div>
                                                            </div>
                                                          </div>`;
    }




    // View task
    function viewTask(taskId) {
      const task = tasks.find(t => t.id == taskId);
      if (!task) return;

      const checked = task.status === 'done' ? 'checked' : '';

      $('#viewTaskDelete').attr('data-id', task.id);
      $('#viewTaskEdit').attr('data-id', task.id);

      $('#viewTaskCheckbox').attr('data-id', task.id).prop('checked', checked);
      $('#viewTaskTitle').text(task.title);
      $('#viewTaskDescription').text(task.description || 'No Description');
      $('#viewTaskCategory').text(task.category?.name || '');
      $('#viewTaskPriority').text(task.priority ? task.priority.charAt(0).toUpperCase() + task.priority.slice(1) : 'N/A');
      $('#viewTaskAssignee').text(task.assignee?.name || 'Unassigned');

      if (task.due_at) {
        const d = new Date(task.due_at);
        $('#viewTaskDueDate').text(d.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));
        $('#viewTaskDueTime').text(d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }));
      } else {
        $('#viewTaskDueDate').text('No Date');
        $('#viewTaskDueTime').text('');
      }

      $('#viewTaskTags').html(Array.isArray(task.tags) && task.tags.length
        ? task.tags.map(tag => `<span class="todo-item-tag">${tag}</span>`).join(' ')
        : '<p>No Tags</p>');

      $('#viewTaskSubtasks').html(Array.isArray(task.subtasks) && task.subtasks.length
        ? task.subtasks.map((st, i) => `<div class="todo-subtask">
                                                              <input type="checkbox" class="todo-subtask-check" id="subtask${st.id}"  data-id="${st.id}" ${st.is_completed ? 'checked' : ''}>
                                                              <label for="subtask${st.id}">${st.title}</label>
                                                            </div>`).join('')
        : '<p>No Subtasks</p>');

      $('#viewTaskActivity').html(Array.isArray(task.activities) && task.activities.length
        ? task.activities.map(act => {
          const profileUrl = act.causer?.profile?.profile_picture || 'assets/img/avatars/avatar-placeholder.webp';
          return `<div class="todo-activity-item">
                                                                        <img src="${profileUrl}" alt="${act.causer?.name || 'User'}">
                                                                        <div class="todo-activity-content">
                                                                          <span class="todo-activity-text"><strong>${act.causer?.name || 'Unknown'}</strong> ${act.description}</span>
                                                                          <span class="todo-activity-time">${new Date(act.created_at).toLocaleString()}</span>
                                                                        </div>
                                                                      </div>`;
        }).join('')
        : '<p>No Activity</p>');

      $('#viewTaskModal').modal('show');
    }

    // Edit task
    function editTask(taskId) {
      const task = tasks.find(t => t.id == taskId);
      if (!task) return;

      $('#editTaskForm').data('task-id', task.id);
      $('#editTaskTitle').val(task.title);
      $('#editTaskDescription').val(task.description || '');
      $('#editTaskDueDate').val(task.due_at ? new Date(task.due_at).toISOString().slice(0, 10) : '');
      $('#editTaskDueTime').val(task.due_at ? new Date(task.due_at).toTimeString().slice(0, 5) : '');

      $('#editTaskDelete').attr('data-id', task.id);

      $('#editTaskPriority').val(task.priority || 'medium');


      $('#editTaskTags').val(task.tags?.join(', ') || '');

      const $subtasks = $('#editTaskSubtasks').empty();
      task.subtasks?.forEach(st => {
        $subtasks.append(`<div class="todo-edit-subtask">
                                                            <input type="text" class="form-control" value="${st.title}">
                                                            <button type="button" class="todo-edit-subtask-remove">
                                                              <i class="bi bi-x-lg"></i>
                                                            </button>
                                                          </div>`);
      });

      $('#editTaskModal').modal('show');
    }

    // Delete task
    function deleteTask(taskId) {
      if (!confirm('Delete this task?')) return;

      $.ajax({
        url: `/task/${taskId}`,
        type: 'DELETE',
        success: function () {
          getTasks();
        }
      });
    }

    // Save task changes
    function saveTask() {
      const taskId = $('#editTaskForm').data('task-id');

      const data = {
        title: $('#editTaskTitle').val(),
        description: $('#editTaskDescription').val(),
        due_at: $('#editTaskDueDate').val()
          ? `${$('#editTaskDueDate').val()}T${$('#editTaskDueTime').val() || '00:00'}:00`
          : null,
        category_id: $('#editTaskProject').val() ? parseInt($('#editTaskProject').val()) : null,
        priority: $('#editTaskPriority').val(),
        assigned_to: $('#editTaskAssignee').val() ? parseInt($('#editTaskAssignee').val()) : null,
        tags: $('#editTaskTags').val()
          .split(',')
          .map(t => t.trim())
          .filter(t => t),
        subtasks: []
      };

      // Collect subtasks and force is_completed to boolean
      $('#editTaskSubtasks .todo-edit-subtask').each(function () {
        const input = $(this).find('input[type="text"]').val().trim();
        const completed = !!$(this).find('input[type="checkbox"]').prop('checked'); // <-- ensure boolean
        const subtaskId = $(this).data('subtask-id');

        if (input) {
          data.subtasks.push({
            id: subtaskId || undefined,
            title: input,
            is_completed: completed
          });
        }
      });

      $.ajax({
        url: `/task/${taskId}`,
        type: 'PUT',
        contentType: 'application/json', // send JSON
        data: JSON.stringify(data),
        success: function (updatedTask) {
          // Update global array
          const index = tasks.findIndex(t => t.id == taskId);
          if (index > -1) tasks[index] = { ...tasks[index], ...updatedTask };

          renderTasks(tasks); // rerender updated task list
          $('#editTaskModal').modal('hide');
        },
        error: function (xhr) {
          console.error('Failed to save task:', xhr.responseText);
          alert('Error saving task. Check console for details.');
        }
      });
    }



    function createTask() {
      const data = {
        title: $('#addTaskForm').find('input[name="title"]').val(),
        description: $('#addTaskForm').find('textarea[name="description"]').val(),
        due_at: $('#addTaskDueDate').val()
          ? `${$('#addTaskDueDate').val()}T${$('#addTaskDueTime').val() || '00:00'}:00`
          : null,
        priority: $('#addTaskPriority').val(),
        category_id: $('#addTaskCategory').val(),
        assigned_to: $('#addTaskAssignee').val() ? parseInt($('#addTaskAssignee').val()) : null,
        tags: $('#addTaskTags').val()
          .split(',')
          .map(t => t.trim())
          .filter(t => t),
        subtasks: []
      };

      // Collect subtasks
      $('#addTaskSubtasks input').each(function () {
        const title = $(this).val().trim();
        if (title) data.subtasks.push({ title: title, is_completed: false });
      });

      $.ajax({
        url: '{{ route("task.store") }}',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function (newTask) {
      getTasks();
          $('#addTaskModal').modal('hide');
          $('#addTaskForm')[0].reset();
          $('#addTaskSubtasks').empty();
        },
        error: function (xhr) {
          console.error('Failed to create task:', xhr.responseText);
          alert('Error creating task. Check console for details.');
        }
      });
    }


    // --------------------------------------
    // Event bindings
    // --------------------------------------
    $(document).on('click', '.todo-view-btn-trigger', e => { e.preventDefault(); viewTask($(e.currentTarget).data('id')); });
    $(document).on('click', '.todo-edit-btn-trigger', e => { e.preventDefault(); editTask($(e.currentTarget).data('id')); });
    $(document).on('click', '#viewTaskEdit', function (e) {
      e.preventDefault();

      const id = $(this).data('id');

      // Close the current modal
      $('#viewTaskModal').modal('hide');

      editTask($(e.currentTarget).data('id'));
    });
    $(document).on('click', '.todo-delete-btn-trigger', e => { e.preventDefault(); deleteTask($(e.currentTarget).data('id')); });
    $('#saveTaskBtn').click(saveTask);
    $('#createTaskBtn').on('click', function (e) {
      e.preventDefault();
      createTask();
    });


    $('#addSubtaskBtn').click(() => {
      $('#editTaskSubtasks').append(`<div class="todo-edit-subtask">
                                                            <input type="text" class="form-control" placeholder="New subtask">
                                                            <button type="button" class="todo-edit-subtask-remove"><i class="bi bi-x-lg"></i></button>
                                                          </div>`);
    });



    $(document).on('change', '.todo-item-check input[type="checkbox"]', function () {
      const $taskItem = $(this).closest('.todo-item');
      const taskId = $taskItem.data('id');
      const isDone = $(this).is(':checked');

      $.ajax({
        url: `/task/${taskId}/toggle`,
        type: 'PATCH',
        contentType: 'application/json',
        data: JSON.stringify({ status: isDone ? 'done' : 'todo' }),
        success: function (updatedTask) {
          // Update global array and re-render
          const index = tasks.findIndex(t => t.id == taskId);
          if (index > -1) tasks[index].status = updatedTask.status;
          renderTasks(tasks);
        },
        error: function (xhr) {
          console.error('Failed to update task status:', xhr.responseText);
          alert('Failed to update task status');
        }
      });
    });


    $(document).on('change', '.todo-checkbox', function () {
      const $checkbox = $(this);
      const taskId = $checkbox.data('id');
      const isDone = $checkbox.is(':checked');

      $.ajax({
        url: `/task/${taskId}/toggle`,
        type: 'PATCH',
        contentType: 'application/json',
        data: JSON.stringify({ status: isDone ? 'done' : 'todo' }),
        success: function (updatedTask) {
          // Update global array and re-render
          // console.log('test2');
          const index = tasks.findIndex(t => t.id == taskId);
          if (index > -1) tasks[index].status = updatedTask.status;
          renderTasks(tasks);
        },
        error: function (xhr) {
          console.error('Failed to update task status:', xhr.responseText);
          alert('Failed to update task status');
        }
      });
    });



    // Toggle subtask done inside View Task modal
    $(document).on('change', '.todo-subtask-check', function () {
      const $subtask = $(this).closest('.todo-subtask');
      const subtaskId = $subtask.find('.todo-subtask-check').attr('data-id');
      // console.log('subtaskId:', subtaskId);
      const isCompleted = $(this).is(':checked');

      $.ajax({
        url: `/task/subtask/${subtaskId}/toggle`,
        type: 'PATCH',
        contentType: 'application/json',
        data: JSON.stringify({ is_completed: isCompleted }),
        success: function (updatedSubtask) {
          // Update global tasks array
          tasks.forEach(task => {
            const stIndex = task.subtasks.findIndex(st => st.id == updatedSubtask.id);
            if (stIndex > -1) task.subtasks[stIndex].is_completed = updatedSubtask.is_completed;
          });

          renderTasks(tasks);
        },
        error: function (xhr) {
          console.error('Failed to toggle subtask:', xhr.responseText);
          alert('Failed to update subtask');
        }
      });
    });



    $(document).on('click', '.todo-edit-subtask-remove', function () { $(this).closest('.todo-edit-subtask').remove(); });






    $(document).on('click', '#viewTaskDelete', function () {
      const taskId = $(this).data('id');

      if (!confirm('Are you sure you want to delete this task?')) {
        return;
      }

      $.ajax({
        url: `/task/${taskId}`,
        type: 'DELETE',
        success: function (response) {
          console.log(response.message);

          // Remove from global tasks array
          tasks = tasks.filter(t => t.id != taskId);

          // Re-render task list
          renderTasks(tasks);

          // Close modal
          $('#viewTaskModal').modal('hide');
        },
        error: function (xhr) {
          console.error('Failed to delete task:', xhr.responseText);
          alert('Failed to delete task');
        }
      });
    });


    $(document).on('click', '#editTaskDelete', function () {
      const taskId = $(this).data('id');

      if (!confirm('Are you sure you want to delete this task?')) {
        return;
      }

      $.ajax({
        url: `/task/${taskId}`,
        type: 'DELETE',
        success: function (response) {
          console.log(response.message);

          // Remove from global tasks array
          tasks = tasks.filter(t => t.id != taskId);

          // Re-render task list
          renderTasks(tasks);

          // Close modal
          $('#editTaskModal').modal('hide');
        },
        error: function (xhr) {
          console.error('Failed to delete task:', xhr.responseText);
          alert('Failed to delete task');
        }
      });
    });




    $(document).on('click', '.todo-item-content', e => {
      e.preventDefault();

      // Get the parent todo-item
      const $item = $(e.currentTarget).closest('.todo-item');
      const taskId = $item.data('id');

      viewTask(taskId);
    });




  </script>





@endpush