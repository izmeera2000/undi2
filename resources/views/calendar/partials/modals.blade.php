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
              <label for="eventDate" class="form-label">Date</label>
              <input type="date" class="form-control" id="eventDate" required>
            </div>
            <div class="col-6">
              <label for="eventTime" class="form-label">Time</label>
              <input type="time" class="form-control" id="eventTime">
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
        <div class="mb-3">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-calendar3 text-muted"></i>
            <span id="eventDetailsDate"></span>
          </div>
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-clock text-muted"></i>
            <span id="eventDetailsTime"></span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-tag text-muted"></i>
            <span class="badge bg-primary" id="eventDetailsCategory"></span>
          </div>
        </div>
        <hr>
        <p class="text-muted mb-0" id="eventDetailsDescription"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger" id="deleteEventBtn">Delete</button>
      </div>
    </div>
  </div>
</div>
