@extends('layouts.app')

@section('title', 'Dashboard')

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

    let allEventsMap = new Map(); // key = event.id
    let selectedEventId = null;

    document.addEventListener('DOMContentLoaded', function () {
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

      const calendarEl = document.getElementById('calendar');
      const upcomingEl = document.getElementById('upcomingEvents');

      const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 750,
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
        firstDay: 1,
        selectable: true,
        editable: true,
        events: "{{ route('events.index') }}",

        select: function (info) {
          // Open create modal and prefill date
          const modal = new bootstrap.Modal(document.getElementById('createEventModal'));

          document.getElementById('eventDate').value = info.startStr;
          modal.show();
          calendar.unselect();
        },

        eventClick: function (info) {
          const event = info.event;
          const currentUserId = {{ auth()->id() }}; // you need to set this meta in your Blade
          const deleteBtn = document.getElementById('deleteEventBtn');
          selectedEventId = event.id;


          // Event title
          document.getElementById('eventDetailsTitle').textContent = event.title;

          // Start date/time
          document.getElementById('eventDetailsStartDate').textContent =
            event.start.toLocaleDateString();
          document.getElementById('eventDetailsStartTime').textContent =
            event.allDay ? 'All day' : event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

          // End date/time
          if (event.end) {
            document.getElementById('eventDetailsEndDate').textContent =
              event.end.toLocaleDateString();
            document.getElementById('eventDetailsEndTime').textContent =
              event.allDay ? '' : event.end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
          } else {
            document.getElementById('eventDetailsEndDate').textContent = '—';
            document.getElementById('eventDetailsEndTime').textContent = '';
          }

          // Description
          document.getElementById('eventDetailsDescription').textContent =
            event.extendedProps.description ?? 'No description';

          // Participants
          const container = document.getElementById('eventDetailsParticipants');
          container.innerHTML = '';
          (event.extendedProps.participants || []).forEach(p => {
            container.innerHTML += `<span class="badge bg-secondary">${p.name}</span>`;
          });

          // Show or hide Delete button
          if (event.extendedProps.created_by === currentUserId) {
            deleteBtn.style.display = 'inline-block';
          } else {
            deleteBtn.style.display = 'none';
          }

          // Show modal
          new bootstrap.Modal(document.getElementById('eventDetailsModal')).show();
        },



        eventDrop: function (info) {
          fetch(`/events/${info.event.id}`, {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
              start_date: info.event.start.toISOString(),
              end_date: info.event.end ? info.event.end.toISOString() : info.event.start.toISOString()
            })
          });
        },
        eventsSet: function (events) {
          events.forEach(event => {
            allEventsMap.set(event.id, event);
          });
          renderUpcomingEvents();
        },

        eventsChange: function (events) {

          events.forEach(event => {
            allEventsMap.set(info.event.id, info.event);
          });
          renderUpcomingEvents();
        },
        eventsAdd: function (events) {

          events.forEach(event => {
            allEventsMap.set(info.event.id, info.event);
          });
          renderUpcomingEvents();
        },
      });

      calendar.render();

      // Handle create modal save button
      document.getElementById('saveEventBtn').addEventListener('click', () => {

        const form = document.getElementById('createEventForm');
        if (!form.reportValidity()) return;

        const title = document.getElementById('eventTitle').value;
        const date = document.getElementById('eventDate').value;
        const time = document.getElementById('eventTime').value || '00:00';

        const endDate = document.getElementById('eventEndDate').value;
        const endTime = document.getElementById('eventEndTime').value || '00:00';

        const start_date = `${date} ${time}`;
        const end_date = endDate ? `${endDate} ${endTime}` : null;

        const participants = Array.from(
          document.querySelectorAll('.participant-checkbox:checked')
        ).map(cb => cb.value);


 

        console.log(title);
        console.log(start_date);
        console.log(end_date);
        console.log(participants);
 

        fetch('/events', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: JSON.stringify({
            title,
            start_date,
            end_date,
            participants,
            all_day: document.getElementById('eventAllDay').checked,
            description: document.getElementById('eventDescription').value
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