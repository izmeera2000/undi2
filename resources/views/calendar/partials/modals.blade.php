<!-- Create Event Modal -->
<div class="modal fade" id="createEventModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="createEventForm">
          <div class="mb-3">
            <label for="eventTitle" class="form-label">Event Title</label>
            <input type="text" class="form-control" id="eventTitle" placeholder="Enter event title" required>
          </div>
          <div class="row mb-3">
            <div class="col-6">
              <label for="eventDate" class="form-label">Start Date</label>
              <input type="date" class="form-control" id="eventDate" required>
            </div>
            <div class="col-6">
              <label for="eventTime" class="form-label">Start Time</label>
              <input type="time" class="form-control" id="eventTime">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-6">
              <label for="eventEndDate" class="form-label">End Date</label>
              <input type="date" class="form-control" id="eventEndDate">
            </div>
            <div class="col-6">
              <label for="eventEndTime" class="form-label">End Time</label>
              <input type="time" class="form-control" id="eventEndTime">
            </div>
          </div>


          <div class="mb-3">
            <label class="form-label">Participants</label>

            <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
              @foreach($users as $user)
                <div class="form-check">
                  <input class="form-check-input participant-checkbox" type="checkbox" value="{{ $user->id }}"
                    id="participant{{ $user->id }}">
                  <label class="form-check-label" for="participant{{ $user->id }}">
                    {{ $user->name }}
                  </label>
                </div>
              @endforeach
            </div>
          </div>


          <div class="mb-3">
            <label for="eventDescription" class="form-label">Description</label>
            <textarea class="form-control" id="eventDescription" rows="3"></textarea>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="eventAllDay">
            <label class="form-check-label" for="eventAllDay">All day event</label>
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
        <button type="button" class="btn btn-outline-danger" id="deleteEventBtn">
          Delete
        </button>
      </div>
    </div>
  </div>
</div>