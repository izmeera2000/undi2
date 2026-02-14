@extends('layouts.app')

@section('title', 'Events')

@push('styles')


@endpush










@section('content')

  <div class="calendar-app">
    <!-- Sidebar -->


    <button class="calendar-sidebar-toggle" id="calendarSidebarToggle" aria-label="Toggle calendar sidebar">
      <i class="bi bi-calendar3"></i>
      <span>Sidebar</span>
    </button>

    <div class="calendar-sidebar-overlay" id="calendarSidebarOverlay"></div>


    <div class="calendar-sidebar" id="calendarSidebar">
      <!-- Create Event Button -->
      <button class="btn btn-primary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#createEventModal">
        <i class="bi bi-plus-lg me-2"></i> Create Event
      </button>

      <!-- Upcoming Events -->
      <div class="card mb-3">
        <div class="card-header">Upcoming Events</div>
        <div class="card-body p-2" id="upcomingEvents">
          <!-- JS will populate this -->
        </div>
      </div>



    </div>




    <!-- Main Calendar -->
    <div class="flex-grow-1 p-3">
      <div id="calendar" class="calendar-main"></div>
    </div>




  </div>

  @include('calendar.partials.modals') <!-- Include your modals here -->

@endsection

@push('scripts')

  <script src="{{ asset('assets/vendors/fullcalendar/fullcalendar.min.js') }}"></script>

  <script>

    function updateEventDates(info) {

      fetch(`/events/${info.event.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          start_date: info.event.start.toISOString(),
          end_date: info.event.end
            ? info.event.end.toISOString()
            : null
        })
      })
        .then(res => res.json())
        .then(data => {
          if (!data.success) {
            info.revert(); // revert if failed
            alert('Update failed');
          }
        })
        .catch(() => {
          info.revert();
          alert('Update error');
        });
    }

    function showEventDetails(event) {

      const currentUserId = {{ auth()->id() }};
      const deleteBtn = document.getElementById('deleteEventBtn');
      const tz = 'Asia/Kuala_Lumpur';

      // =========================
      // TITLE
      // =========================
      document.getElementById('eventDetailsTitle').textContent = event.title;

      const start = event.start;
      const end = event.end;

      const dateRangeEl = document.getElementById('eventDetailsDateRange');
      const timeRangeEl = document.getElementById('eventDetailsTimeRange');

      // =========================
      // FORMAT START DATE
      // =========================
      const startDate = start.toLocaleDateString('en-MY', {
        timeZone: tz,
        day: '2-digit',
        month: 'short',
        year: 'numeric'
      });

      let endDate = null;

      if (end) {
        endDate = end.toLocaleDateString('en-MY', {
          timeZone: tz,
          day: '2-digit',
          month: 'short',
          year: 'numeric'
        });
      }

      // =========================
      // DATE RANGE DISPLAY
      // =========================
      if (!end || startDate === endDate) {
        dateRangeEl.textContent = startDate;
      } else {
        dateRangeEl.textContent = `${startDate} – ${endDate}`;
      }

      // =========================
      // TIME RANGE DISPLAY
      // =========================
      if (event.allDay) {

        timeRangeEl.textContent = 'All Day';

      } else {

        const startTime = start.toLocaleTimeString('en-MY', {
          timeZone: tz,
          hour: '2-digit',
          minute: '2-digit'
        });

        if (!end) {

          timeRangeEl.textContent = startTime;

        } else {

          const endTime = end.toLocaleTimeString('en-MY', {
            timeZone: tz,
            hour: '2-digit',
            minute: '2-digit'
          });

          if (startDate === endDate) {
            timeRangeEl.textContent = `${startTime} – ${endTime}`;
          } else {
            timeRangeEl.textContent = `${startTime} → ${endTime}`;
          }
        }
      }

      // =========================
      // DESCRIPTION
      // =========================
      document.getElementById('eventDetailsDescription').textContent =
        event.extendedProps.description ?? 'No description';

      // =========================
      // PARTICIPANTS
      // =========================
      const container = document.getElementById('eventDetailsParticipants');
      container.innerHTML = '';

      const participants = event.extendedProps.participants || [];

      if (participants.length === 0) {
        container.innerHTML =
          `<span class="text-muted small">No participants</span>`;
      } else {
        participants.forEach(p => {
          container.innerHTML +=
            `<span class="badge bg-secondary">${p.name}</span>`;
        });
      }

      // =========================
      // DELETE BUTTON
      // =========================
      deleteBtn.style.display =
        event.extendedProps.created_by === currentUserId
          ? 'inline-block'
          : 'none';

      // =========================
      // SHOW MODAL
      // =========================
      new bootstrap.Modal(
        document.getElementById('eventDetailsModal')
      ).show();
    }




    let allEventsMap = new Map(); // key = event.id
    let selectedEventId = null;

    document.addEventListener('DOMContentLoaded', function () {
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

      const calendarEl = document.getElementById('calendar');
      const upcomingEl = document.getElementById('upcomingEvents');

      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        timeZone: 'Asia/Kuala_Lumpur',
        height: 750,
        firstDay: 1,
        selectable: true,
        editable: true,
        eventResizableFromStart: true,
        events: "{{ route('events.index') }}",

        headerToolbar: window.innerWidth < 768
          ? {
            left: 'title',
            center: 'prev,today,next',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
          }
          : {
            left: 'prev,today,next',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
          },

        // =============================
        // CLICK DRAG TO CREATE
        // =============================
        select: function (info) {

          const modal = new bootstrap.Modal(
            document.getElementById('createEventModal')
          );

          const start = new Date(info.start);
          const end = new Date(info.end);

          // FullCalendar end is exclusive → subtract 1 day
          end.setDate(end.getDate() - 1);

          // Helper to format local date (YYYY-MM-DD)
          function formatLocalDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
          }

          document.getElementById('eventDate').value =
            formatLocalDate(start);

          document.getElementById('eventEndDate').value =
            formatLocalDate(end);

          document.getElementById('eventAllDay').checked = info.allDay;

          modal.show();
          calendar.unselect();
        },



        // =============================
        // DRAG MOVE EVENT
        // =============================
        eventDrop: function (info) {
          updateEventDates(info);
        },

        // =============================
        // RESIZE EVENT
        // =============================
        eventResize: function (info) {
          updateEventDates(info);
        },

        // =============================
        // LOAD / REFRESH EVENTS
        // =============================
        eventsSet: function (events) {
          allEventsMap.clear();
          events.forEach(event => {
            allEventsMap.set(event.id, event);
          });
          renderUpcomingEvents();
        },

        eventClick: function (info) {
          selectedEventId = info.event.id;
          showEventDetails(info.event);
        }

      });




      calendar.render();

      // Handle create modal save button
      document.getElementById('saveEventBtn').addEventListener('click', () => {

        const form = document.getElementById('createEventForm');
        if (!form.reportValidity()) return;

        const title = document.getElementById('eventTitle').value;
        const date = document.getElementById('eventDate').value;
        const endDateInput = document.getElementById('eventEndDate').value;

        let startTime = document.getElementById('eventTime').value;
        let endTime = document.getElementById('eventEndTime').value;

        let allDay = false;
        let startDateTime;
        let endDateTime;

        // Helper to format local datetime → YYYY-MM-DD HH:MM:SS
        function formatDateTime(dateObj) {
          const y = dateObj.getFullYear();
          const m = String(dateObj.getMonth() + 1).padStart(2, '0');
          const d = String(dateObj.getDate()).padStart(2, '0');
          const h = String(dateObj.getHours()).padStart(2, '0');
          const min = String(dateObj.getMinutes()).padStart(2, '0');
          const s = String(dateObj.getSeconds()).padStart(2, '0');
          return `${y}-${m}-${d} ${h}:${min}:${s}`;
        }

        const start = new Date(date);
        const end = new Date(endDateInput);

        // ==========================
        // RULE 1: No start time = All Day
        // ==========================
        if (!startTime) {

          allDay = true;

          start.setHours(0, 0, 0, 0);
          end.setHours(23, 59, 59, 999);

        }
        else {

          // If start time exists
          const [sh, sm] = startTime.split(':');
          start.setHours(sh, sm, 0, 0);

          if (!endTime) {
            // RULE 2: Start time only → until 23:59
            end.setHours(23, 59, 59, 999);
          } else {
            // RULE 3: Start + End time normal
            const [eh, em] = endTime.split(':');
            end.setHours(eh, em, 0, 0);
          }
        }

        startDateTime = formatDateTime(start);
        endDateTime = formatDateTime(end);

        fetch('/events', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            title,
            start_date: startDateTime,
            end_date: endDateTime,
            all_day: allDay,
            description: document.getElementById('eventDescription').value,
            participants: Array.from(
              document.querySelectorAll('.participant-checkbox:checked')
            ).map(cb => cb.value)
          })
        })
          .then(res => res.json())
          .then(() => {
            bootstrap.Modal.getInstance(
              document.getElementById('createEventModal')
            ).hide();
            calendar.refetchEvents();
            form.reset();
          });

      });


      document.getElementById('deleteEventBtn').addEventListener('click', function () {
        if (!selectedEventId) return;

        if (!confirm('Delete this event?')) return;

        fetch(`/events/${selectedEventId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          }
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              bootstrap.Modal.getInstance(document.getElementById('eventDetailsModal')).hide();
              calendar.refetchEvents();
              selectedEventId = null;
            }
          })
          .catch(err => {
            console.error(err);
            alert('Failed to delete event');
          });
      });



      function renderUpcomingEvents() {
        const allEvents = Array.from(allEventsMap.values());

        if (!allEvents.length) {
          upcomingEl.innerHTML = '<small>No upcoming events</small>';
          return;
        }

        const now = new Date();
        now.setHours(0, 0, 0, 0);

        const futureEvents = allEvents
          .map(e => ({
            ...e,
            start: e.start instanceof Date ? e.start : new Date(e.start),
            end: e.end ? (e.end instanceof Date ? e.end : new Date(e.end)) : null
          }))
          .filter(e => e.start >= now)
          .sort((a, b) => a.start - b.start)
          .slice(0, 5);

        upcomingEl.innerHTML = '';

        if (!futureEvents.length) {
          upcomingEl.innerHTML = '<small>No upcoming events</small>';
          return;
        }

        futureEvents.forEach(event => {

          console.log(futureEvents);
          const startDate = new Date(event.start);





          const endDate = event.end ? new Date(event.end) : null;

          const title = event._def.title;
          const day = startDate.getDate();
          const date = event.start;


          let timeText = '';
          if (event._def.allDay) {
            timeText = 'All Day';
          } else {
            const startTime = startDate.toLocaleTimeString('en-MY', { hour: '2-digit', minute: '2-digit' });
            const endTime = endDate ? endDate.toLocaleTimeString('en-MY', { hour: '2-digit', minute: '2-digit' }) : '';
            timeText = endTime ? `${startTime} - ${endTime}` : startTime;
          }




          const month = date.toLocaleString('default', { month: 'short' });
          const time = event.allDay
            ? 'All Day'
            : date.toLocaleTimeString('en-MY', { hour: '2-digit', minute: '2-digit' });

          upcomingEl.insertAdjacentHTML(
            'beforeend',
            `
                                        <div class="upcoming-event-item">
                                          <div class="upcoming-event-color"
                                                style="background:${event.backgroundColor || '#0d6efd'}; width: 8px;"></div>
                                          <div class="upcoming-event-date text-center me-2">
                                            <div class="fw-bold">${day}</div>
                                            <div>${month}</div>
                                          </div>
                                          <div>
                                            <div class="fw-semibold">${title}</div>
                                            <div class="upcoming-event-time"><i class="bi bi-clock me-1"></i>${timeText}</div>
                                            </div>
                                        </div>
                                      `
          );
        });



      }



      // Call whenever events change or calendar navigates
      calendar.on('eventAdd', renderUpcomingEvents);
      calendar.on('eventChange', renderUpcomingEvents);
      calendar.on('eventRemove', renderUpcomingEvents);



    });





  </script>










@endpush