@extends('layouts.app')

@section('title', 'Dashboard')




@section('breadcrumb')
  @php
    // Build dynamic crumbs based on request
    $crumbs = [

    ];

  @endphp

@endsection

@section('content')

  <div class="row g-4">

    <div class=" col col-md-6">
      <div class="widget-banner-promo light h-100">
        <div class="widget-banner-content">
          <p class="widget-banner-text">Good Day,</p>

          <h4 class="widget-banner-title">David Dev!</h4>
          <div class="welcome-date">

            <i class="bi bi-calendar3"></i>
            <span id="currentDate">Feb 14, 2026</span>


            <i class="bi bi-clock ms-3"></i>
            <span id="currentTime">06:57:32</span>
          </div>
        </div>

      </div>
    </div>


    <div class="col col-md-6 g-4">


      <div class="card widget-weather-image-card">
        <div class="widget-weather-image-bg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
          <div class="widget-weather-image-content">
            <div class="widget-weather-location">Portland, Oregon</div>
            <div class="widget-weather-temp-large">
              <i class="bi bi-cloud-sun"></i>
              <span>18°<small>C / 9°C</small></span>
            </div>
            <div class="widget-weather-day">Saturday</div>
          </div>
        </div>
      </div>

    </div>


    <div class="col-md-4">
      <!-- Upcoming Events -->
      <div class="card mb-3 h-100">
        <div class="card-header">Upcoming Events</div>
        <div class="card-body p-2" id="upcomingEventsA">
          <!-- JS will populate this -->
        </div>
      </div>

    </div>


    <div class="col-lg-8">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title">Tasks</h5>
          <div class="card-actions">

            <a href="{{ route('task.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
          </div>
        </div>
        <div class="card-body">
          <!-- Todo List -->
          <div class="todo-list" id="taskList">


          </div>
        </div>
      </div>
    </div>

    {{--
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Recent Activity</h5>
          <div class="card-actions">
            <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
          </div>
        </div>
        <div class="card-body widget-activity">
          <div class="activity-item">
            <div class="activity-icon primary">
              <i class="bi bi-person-plus"></i>
            </div>
            <div class="activity-content">
              <div class="activity-title"><a href="#">Sarah Johnson</a> joined the team</div>
              <div class="activity-text">New team member added to the Marketing department</div>
              <div class="activity-time">2 minutes ago</div>
            </div>
          </div>
          <div class="activity-item">
            <div class="activity-icon success">
              <i class="bi bi-check-circle"></i>
            </div>
            <div class="activity-content">
              <div class="activity-title">Project <a href="#">Website Redesign</a> completed</div>
              <div class="activity-text">All tasks have been marked as done</div>
              <div class="activity-time">1 hour ago</div>
            </div>
          </div>
          <div class="activity-item">
            <div class="activity-icon warning">
              <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="activity-content">
              <div class="activity-title">Server load warning</div>
              <div class="activity-text">CPU usage exceeded 80% threshold</div>
              <div class="activity-time">3 hours ago</div>
            </div>
          </div>
          <div class="activity-item">
            <div class="activity-icon danger">
              <i class="bi bi-bug"></i>
            </div>
            <div class="activity-content">
              <div class="activity-title">Bug reported in <a href="#">Payment Module</a></div>
              <div class="activity-text">Transaction failed for order #12485</div>
              <div class="activity-time">5 hours ago</div>
            </div>
          </div>
          <div class="activity-item">
            <div class="activity-icon info">
              <i class="bi bi-info-circle"></i>
            </div>
            <div class="activity-content">
              <div class="activity-title">System update available</div>
              <div class="activity-text">Version 2.4.0 is ready for installation</div>
              <div class="activity-time">Yesterday</div>
            </div>
          </div>
        </div>
      </div>
    </div> --}}

  </div>




@endsection

@push('scripts')
  <script>

    const upcomingEl = document.getElementById('upcomingEventsA');


    function updateDateTime() {
      // Create a new Date object
      const now = new Date();

      // Format current date in Malaysia Time (Asia/Kuala_Lumpur)
      const currentDate = now.toLocaleDateString('en-MY', {
        weekday: 'long',   // Full weekday (e.g., Monday)
        month: 'long',     // Full month (e.g., February)
        day: 'numeric',    // Day of the month (e.g., 14)
        year: 'numeric',   // Full year (e.g., 2026)
      });

      // Format current time in Malaysia Time (Asia/Kuala_Lumpur)
      const currentTime = now.toLocaleTimeString('en-MY', {
        hour12: true,        // 12-hour format (with AM/PM)
        hour: '2-digit',     // 2-digit hour
        minute: '2-digit',   // 2-digit minute
        timeZone: 'Asia/Kuala_Lumpur', // Malaysia Timezone (GMT+8)
      });

      // Update the date and time on the page
      document.getElementById('currentDate').textContent = currentDate;
      document.getElementById('currentTime').textContent = currentTime;
    }




    function renderUpcomingEvents() {
      // API call to fetch upcoming events from the server
      fetch('{{ route('events.upcoming') }}', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
      })
        .then(response => {
          console.log('Raw response:', response);  // Log the raw response object

          if (!response.ok) {
            // If the response is not OK (e.g., status code 404 or 500), throw an error
            throw new Error(`HTTP error! status: ${response.status}`);
          }

          return response.json();  // Proceed to convert the response to JSON
        }).then(events => {

          console.log('Events data:', events);  // Log the events data after parsing it as JSON



          if (!events.length) {
            upcomingEl.innerHTML = `
                                <div class="text-center text-muted my-4">
                                  <i class="bi bi-calendar-x fs-2 mb-2"></i> <!-- Bootstrap Icons -->
                                  <p class="mb-0"><strong>No upcoming events</strong></p>
                                  <small>Check back later for new events!</small>
                                </div>
                              `;
            return;
          }

          const now = new Date();
          now.setHours(0, 0, 0, 0); // Set time to midnight for today

          // Filter and sort upcoming events
          const futureEvents = events
            .map(e => ({
              ...e,
              start: new Date(e.start),
              end: e.end ? new Date(e.end) : null,
            }))
            .filter(e => e.start >= now)
            .sort((a, b) => a.start - b.start)
            .slice(0, 5);

          upcomingEl.innerHTML = ''; // Clear the content before adding new events

          if (!futureEvents.length) {
            upcomingEl.innerHTML = `
                                <div class="text-center text-muted my-4">
                                  <i class="bi bi-calendar-x fs-2 mb-2"></i> <!-- Bootstrap Icons -->
                                  <p class="mb-0"><strong>No upcoming events</strong></p>
                                  <small>Check back later for new events!</small>
                                </div>
                              `;
            return;
          }


          // Loop through the upcoming events and display them
          futureEvents.forEach(event => {
            const startDate = event.start;
            const endDate = event.end;

            const day = startDate.getDate();
            const month = startDate.toLocaleString('default', { month: 'short' });
            const timeText = event.allDay
              ? 'All Day'
              : `${startDate.toLocaleTimeString('en-MY', { hour: '2-digit', minute: '2-digit' })} - ${endDate ? endDate.toLocaleTimeString('en-MY', { hour: '2-digit', minute: '2-digit' }) : ''}`;

            upcomingEl.insertAdjacentHTML(
              'beforeend',
              `
                                                    <div class="upcoming-event-item">
                                                        <div class="upcoming-event-color" style="background:${event.backgroundColor || '#0d6efd'}; width: 8px;"></div>
                                                        <div class="upcoming-event-date text-center me-2">
                                                            <div class="fw-bold">${day}</div>
                                                            <div>${month}</div>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">${event.title}</div>
                                                            <div class="upcoming-event-time"><i class="bi bi-clock me-1"></i>${timeText}</div>
                                                        </div>
                                                    </div>
                                                    `
            );
          });
        })
        .catch(error => {
          console.error('Error fetching events:', error);
          upcomingEl.innerHTML = '<small>Error loading events</small>';
        });
    }




    function fetchWeather() {
      const location = 'Pasir Mas'; // optional, can be dynamic
      const weatherApi = "{{ route('weather.today', ['location' => '__location__']) }}".replace('__location__', encodeURIComponent(location));

      fetch(weatherApi)
        .then(res => res.json())
        .then(data => {
          if (!data) return;

          document.querySelector(".widget-weather-location").textContent = data.location_name;
          document.querySelector(".widget-weather-temp-large span").textContent = `${data.max_temp}° / ${data.min_temp}°`;

          const date = new Date(data.forecast_date);
          document.querySelector(".widget-weather-day").textContent = date.toLocaleDateString("en-MY", { weekday: 'long' });

          // document.querySelector(".widget-weather-summary").textContent = data.summary_forecast || '';
        })
        .catch(err => console.error("Error fetching weather:", err));
    }


    document.addEventListener('DOMContentLoaded', function () {

      // Call the function once to initialize the date and time
      updateDateTime();

      // Set an interval to update the time every second
      setInterval(updateDateTime, 1000);
      renderUpcomingEvents();
      fetchWeather();


    });




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
      const sections = { Today: [], Overdue: [] };



      tasks.forEach(task => {
        if (task.status === 'todo') {
          const taskDate = task.due_at ? task.due_at.split('T')[0] : null;

          if (taskDate === today) {
            sections.Today.push(task);  // Tasks due today
          } else if (taskDate < today) {
            sections.Overdue.push(task);  // Tasks overdue (before today)
          }
        }
      });




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


                                                                        <div class="todo-item-content">
                                                                          <div class="todo-item-title">${task.title}</div>
                                                                          <div class="todo-item-meta">
                                                                            ${task.category ? `<span class="todo-item-project">${task.category.name}</span>` : ''}
                                                                            ${relativeTime ? `<span class="todo-item-due"><i class="bi bi-calendar"></i> ${relativeTime}</span>` : ''}
                                                                            ${tagsHtml}
                                                                            ${task.assignee ? `<span class="todo-item-assignee">Assigned to: ${task.assignee.name}</span>` : ''}
                                                                          </div>
                                                                        </div>

                                                                      </div>`;
    }






    $(document).on('click', '.todo-item-content', e => {
      e.preventDefault();

      // Generate the task route dynamically
      const taskRoute = `/task/${taskId}`;  // Adjust this to your actual task route if needed

      // Navigate to the task page
      window.location.href = taskRoute;
    });



  </script>


@endpush