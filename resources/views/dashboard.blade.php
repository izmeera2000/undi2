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
            <div class="card-body p-2" id="upcomingEvents">
              <!-- JS will populate this -->
            </div>
          </div>

    </div>


    <div class="col-lg-8">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title">Tasks</h5>
          <div class="card-actions">
            <button class="btn btn-sm btn-primary"><i class="bi bi-plus me-1"></i>Add Task</button>
          </div>
        </div>
        <div class="card-body">
          <div class="task-list">
            <div class="task-item">
              <div class="task-checkbox">
                <input type="checkbox" id="task1">
                <label for="task1"></label>
              </div>
              <div class="task-info">
                <div class="task-title">Review dashboard design mockups</div>
                <div class="task-meta">
                  <span class="task-due"><i class="bi bi-calendar"></i> Today</span>
                  <span class="badge badge-soft-danger">High</span>
                </div>
              </div>
            </div>
            <div class="task-item">
              <div class="task-checkbox">
                <input type="checkbox" id="task2" checked="">
                <label for="task2"></label>
              </div>
              <div class="task-info">
                <div class="task-title">Team standup meeting at 10 AM</div>
                <div class="task-meta">
                  <span class="task-due"><i class="bi bi-clock"></i> 10:00 AM</span>
                  <span class="badge badge-soft-warning">Medium</span>
                </div>
              </div>
            </div>
            <div class="task-item">
              <div class="task-checkbox">
                <input type="checkbox" id="task3">
                <label for="task3"></label>
              </div>
              <div class="task-info">
                <div class="task-title">Prepare quarterly report</div>
                <div class="task-meta">
                  <span class="task-due"><i class="bi bi-calendar"></i> Tomorrow</span>
                  <span class="badge badge-soft-primary">Normal</span>
                </div>
              </div>
            </div>
            <div class="task-item">
              <div class="task-checkbox">
                <input type="checkbox" id="task4">
                <label for="task4"></label>
              </div>
              <div class="task-info">
                <div class="task-title">Update user documentation</div>
                <div class="task-meta">
                  <span class="task-due"><i class="bi bi-calendar"></i> Jan 25</span>
                  <span class="badge badge-soft-success">Low</span>
                </div>
              </div>
            </div>
            <div class="task-item">
              <div class="task-checkbox">
                <input type="checkbox" id="task5" checked="">
                <label for="task5"></label>
              </div>
              <div class="task-info">
                <div class="task-title">Fix authentication bug</div>
                <div class="task-meta">
                  <span class="task-due"><i class="bi bi-check-circle text-success"></i> Completed</span>
                  <span class="badge badge-soft-danger">High</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


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
    </div>

  </div>




@endsection

@push('scripts')
  <script>
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
        .then(response => response.json())
        .then(events => {
          const upcomingEl = document.getElementById('upcomingEvents');


          if (!events.length) {
            upcomingEl.innerHTML = '<small>No upcoming events</small>';
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
            upcomingEl.innerHTML = '<small>No upcoming events</small>';
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


    document.addEventListener('DOMContentLoaded', function () {

      // Call the function once to initialize the date and time
      updateDateTime();

      // Set an interval to update the time every second
      setInterval(updateDateTime, 1000);
      renderUpcomingEvents();

    });


  </script>


@endpush