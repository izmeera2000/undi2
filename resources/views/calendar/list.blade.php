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


    const eventAllDay = document.getElementById('eventAllDay');
    const eventTime = document.getElementById('eventTime');
    const eventEndTime = document.getElementById('eventEndTime');

    // Toggle time inputs for all-day events
    eventAllDay.addEventListener('change', () => {
      if (eventAllDay.checked) {
        eventTime.disabled = true;
        eventEndTime.disabled = true;
        eventTime.value = '';
        eventEndTime.value = '';
      } else {
        eventTime.disabled = false;
        eventEndTime.disabled = false;
      }
    });

    // Participant search
    const participantSearch = document.getElementById('participantSearch');
    participantSearch.addEventListener('input', () => {
      const filter = participantSearch.value.toLowerCase();
      document.querySelectorAll('.participant-checkbox').forEach(cb => {
        const label = cb.nextElementSibling.textContent.toLowerCase();
        cb.parentElement.style.display = label.includes(filter) ? '' : 'none';
      });
    });





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
            toastr.error('Update failed');
          }
        })
        .catch(() => {
          info.revert();
          toastr.error('Update error');
        });
    }

    function showEventDetails(event) {

      const currentUserId = {{ auth()->id() }};
      const deleteBtn = document.getElementById('deleteEventBtn');
      const tz = 'Asia/Kuala_Lumpur';
      const canDeleteOwn = {{ auth()->user()->can('event.delete') ? 'true' : 'false' }};
      const canDeleteOthers = {{ auth()->user()->can('event.delete.others') ? 'true' : 'false' }};
      if ((canDeleteOwn && event.extendedProps.created_by === currentUserId) || canDeleteOthers) {
        deleteBtn.classList.remove('d-none'); // show
      } else {
        deleteBtn.classList.add('d-none');    // hide
      }
      // =========================
      // TITLE
      // =========================
      document.getElementById('eventDetailsTitle').textContent = event.title;
      const creatorEl = document.getElementById('eventDetailsCreatedBy');
      creatorEl.textContent = event.extendedProps.creator?.name ?? 'Unknown';
      if (event.extendedProps.created_by === currentUserId) {
        creatorEl.textContent += ' (You)';
      }
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

    $(document).ready(function () {
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

      document.getElementById('saveEventBtn').addEventListener('click', () => {
        const form = document.getElementById('createEventForm');
        if (!form.reportValidity()) return; // Basic HTML5 validation

        const title = document.getElementById('eventTitle').value.trim();
        const date = document.getElementById('eventDate').value;
        const endDateInput = document.getElementById('eventEndDate').value;
        const startTime = document.getElementById('eventTime').value;
        const endTime = document.getElementById('eventEndTime').value;
        const description = document.getElementById('eventDescription').value.trim();
        const color = document.getElementById('eventColor')?.value || '#3788d8'; // default color

        const participants = Array.from(
          document.querySelectorAll('.participant-checkbox:checked')
        ).map(cb => cb.value);

        // Validate required fields
        if (!title) {
          toastr.error('Event title is required!');
          return;
        }
        if (!date) {
          toastr.error('Start date is required!');
          return;
        }

        let allDay = false;
        let startDateTime, endDateTime;

        function formatDateTime(dateObj) {
          const y = dateObj.getFullYear();
          const m = String(dateObj.getMonth() + 1).padStart(2, '0');
          const d = String(dateObj.getDate()).padStart(2, '0');
          const h = String(dateObj.getHours()).padStart(2, '0');
          const min = String(dateObj.getMinutes()).padStart(2, '0');
          const s = String(dateObj.getSeconds()).padStart(2, '0');
          return `${y}-${m}-${d} ${h}:${min}:${s}`;
        }

        // Parse start and end dates
        const start = new Date(date);
        const end = endDateInput ? new Date(endDateInput) : new Date(date);

        // ==========================
        // RULE 1: No start time → All day
        // ==========================
        if (!startTime) {
          allDay = true;
          start.setHours(0, 0, 0, 0);
          end.setHours(23, 59, 59, 999);
        } else {
          // Start time exists
          const [sh, sm] = startTime.split(':');
          start.setHours(sh, sm, 0, 0);

          if (!endTime) {
            // Start time only → end at 23:59 of end date
            end.setHours(23, 59, 59, 999);
          } else {
            // Start + End time normal
            const [eh, em] = endTime.split(':');
            end.setHours(eh, em, 0, 0);
          }
        }

        startDateTime = formatDateTime(start);
        endDateTime = formatDateTime(end);

        // Send to backend
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
            description,
            color,
            participants
          })
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              // Hide modal and refresh calendar
              bootstrap.Modal.getInstance(
                document.getElementById('createEventModal')
              ).hide();
              calendar.refetchEvents();
              form.reset();
              toastr.success('Event created successfully!');

            } else {
              toastr.error('Failed to create event. Please try again.');
            }
          })
          .catch(err => {
            console.error(err);
            toastr.error('Error creating event. Check console for details.');
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
            toastr.error('Failed to delete event');
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

          // console.log(futureEvents);
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