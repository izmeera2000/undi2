<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header  ">
        <h5 class="modal-title">Create Event</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="createEventForm">
          <!-- Event Title -->
          <div class="mb-3">
            <label for="eventTitle" class="form-label fw-semibold">Event Title</label>
            <input type="text" class="form-control" id="eventTitle" placeholder="Enter event title" required>
          </div>

          <!-- Date & Time -->
          <div class="row mb-3">
            <div class="col-md-6 mb-2">
              <label for="eventDate" class="form-label fw-semibold">Start Date</label>
              <input type="date" class="form-control" id="eventDate" required>
            </div>
            <div class="col-md-6 mb-2">
              <label for="eventTime" class="form-label fw-semibold">Start Time</label>
              <input type="time" class="form-control" id="eventTime">
            </div>
            <div class="col-md-6 mb-2">
              <label for="eventEndDate" class="form-label fw-semibold">End Date</label>
              <input type="date" class="form-control" id="eventEndDate">
            </div>
            <div class="col-md-6 mb-2">
              <label for="eventEndTime" class="form-label fw-semibold">End Time</label>
              <input type="time" class="form-control" id="eventEndTime">
            </div>
          </div>

          <!-- All Day Event -->
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="eventAllDay">
            <label class="form-check-label fw-semibold" for="eventAllDay">All day event</label>
          </div>

          <div class="mb-3 {{ auth()->user()->can('event.add.others') ? '' : 'd-none' }}">
            <label class="form-label fw-semibold">Participants</label>
            <input type="text" id="participantSearch" class="form-control mb-2" placeholder="Search participants...">
            <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">

              @can('event.add.others')
                @foreach($users as $user)
                  <div class="form-check">
                    <input class="form-check-input participant-checkbox" type="checkbox" value="{{ $user->id }}"
                      id="participant{{ $user->id }}">
                    <label class="form-check-label" for="participant{{ $user->id }}">
                      {{ $user->name }}
                    </label>
                  </div>
                @endforeach
              @endcan
            </div>
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label for="eventDescription" class="form-label fw-semibold">Description</label>
            <textarea class="form-control" id="eventDescription" rows="3" placeholder="Optional description"></textarea>
          </div>

          <!-- Color Picker -->
          <div class="mb-3">
            <label for="eventColor" class="form-label fw-semibold">Event Color</label>
            <input type="color" class="form-control form-control-color" id="eventColor" value="#3788d8"
              title="Choose event color">
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveEventBtn">Create Event</button>
      </div>
    </div>
  </div>
</div>




<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eventDetailsTitle">Event Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <!-- Date & Time -->
        <div class="d-flex align-items-start gap-3 mb-3">
          <div class="fs-4 text-primary">
            <i class="bi bi-calendar-event"></i>
          </div>
          <div>
            <div id="eventDetailsDateRange" class="fw-semibold"></div>
            <div id="eventDetailsTimeRange" class="text-muted small"></div>
          </div>
        </div>

        <!-- Created By -->
        <div class="d-flex align-items-start gap-3 mb-3">
          <div class="fs-4 text-info">
            <i class="bi bi-person-circle"></i>
          </div>
          <div>
            <div class="fw-semibold">Created By</div>
            <div id="eventDetailsCreatedBy" class="text-muted small"></div>
          </div>
        </div>

        <!-- Participants -->
        <div class="mb-3">
          <div class="fw-semibold mb-2">
            <i class="bi bi-people me-1"></i> Participants
          </div>
          <div id="eventDetailsParticipants" class="d-flex flex-wrap gap-2">
            <!-- JS inject badges -->
          </div>
        </div>

        <hr class="my-3">

        <!-- Description -->
        <div class="d-flex align-items-start gap-3">
          <div class="fs-5 text-secondary">
            <i class="bi bi-text-left"></i>
          </div>
          <p class="text-muted mb-0" id="eventDetailsDescription"></p>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger  " id="deleteEventBtn">
          Delete
        </button>
      </div>
    </div>
  </div>
</div>