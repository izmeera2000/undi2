@extends('layouts.app')

@section('title', 'Staff List')

@section('breadcrumb')
  @php
    // Build dynamic crumbs based on request
    $crumbs = [
      ['label' => 'Staff', 'url' => route('staff.list')],
      ['label' => 'List', 'url' => route('staff.list')],
    ];

  @endphp

@endsection


@push('styles')

  <link rel="stylesheet" href="{{ asset('assets/vendors/datatables/datatables.css')}}">

@endpush




@section('content')
  <!-- Welcome & Stats Row -->
  <div class="row g-4 mb-4">


    <section class="section">
      <!-- Stats Cards -->

      <!-- Staff Table -->
      <div class="card g-4 mb-4">
        <div class="card-header">
          <div class="row g-3 align-items-center w-100">
            <div class="col-md-4 col-12">
              <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                  <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="staffSearch" class="form-control border-start-0 ps-0"
                  placeholder="Search staff...">
              </div>
            </div>
            <div class="col-md-8  col-12">
              <div class="d-flex flex-wrap justify-content-md-end gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                  <i class="bi bi-plus-lg me-1"></i> Add Staff
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body p-1">
          <div class="table-responsive">
            <table id="staffTable" class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  
                  <th>Staff</th>
                  <th>Role</th>
                  <th>Joined</th>
                  <th class="text-end pe-4" style="width: 80px;">Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>


      </div>




    </section>



    <!-- Add Staff Modal -->
    <div class="modal fade" id="addStaffModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0 pb-0">
            <div>
              <h5 class="modal-title">Add New Staff</h5>
              <p class="text-muted small mb-0">Staff will set their password on first login</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="addStaffForm">
              <div class="text-center mb-4">
                <div class="avatar-upload">
                  <div class="avatar-preview">
                    <img src="{{ asset('assets/img/avatars/avatar-1.webp') }}" alt="Avatar" id="avatarPreview">
                  </div>
                  <label class="avatar-edit" for="avatarUpload">
                    <i class="bi bi-camera"></i>
                    <input type="file" id="avatarUpload" accept="image/*" class="d-none">
                  </label>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="Enter email address" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                  <option value="">Select role…</option>
                  <option value="admin">Admin</option>
                  <option value="manager">Manager</option>
                  <option value="user">User</option>
                </select>
              </div>


            </form>
          </div>

          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="addStaffBtn">
              <i class="bi bi-plus-lg me-1"></i> Add Staff
            </button>
          </div>
        </div>
      </div>
    </div>




@endsection




  @push('scripts')
    <script src="{{ asset('assets/vendors/datatables/datatables.js') }}"></script>

    <script>
      $(document).ready(function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const table = $('#staffTable').DataTable({
          processing: true,
          serverSide: true,
          ajax: {
            url: "{{ route('staff.data') }}",
            type: "POST",
            headers: {
              'X-CSRF-TOKEN': csrfToken
            },
            error: function (xhr) {
              if (xhr.status === 401) {
                window.location.href = "{{ route('login') }}";
              }
              if (xhr.status == 419) {
                location.reload();
              }
            }
          },
          columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'staff', name: 'name' },
            { data: 'role', name: 'role' },
            { data: 'joined', name: 'created_at' },
            { data: 'actions', orderable: false, searchable: false }
          ],
          searching: true,   // 👈 make sure enabled
          lengthChange: true,
          pageLength: 10
        });

        $('#staffSearch').on('keydown', function (e) {
          if (e.key === "Enter" || e.keyCode === 13) {
            table.search(this.value).draw();
          }
        });


      });




      $('#addStaffBtn').on('click', function (e) {
        e.preventDefault();

        let form = $('#addStaffForm')[0];
        let formData = new FormData(form);

        $.ajax({
          url: "{{ route('staff.store') }}",
          method: "POST",
          data: formData,
          processData: false,
          contentType: false,
          headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
          success: function (res) {
            $('#addStaffModal').modal('hide');
            $('#staffTable').DataTable().ajax.reload();
            toastr.success("Staff added successfully! They will set their password on first login.");

          },
          error: function (err) {
            console.log(err);
            toastr.error("Error adding staff.");

          }
        });
      });



      // Delete staff via AJAX
      $('#staffTable').on('click', '.delete-staff-btn', function () {
        let staffId = $(this).data('id');

        if (!confirm('Are you sure you want to delete this staff?')) return;

        $.ajax({
          url: `/staff/${staffId}`, // matches Route::resource('staff')
          type: 'DELETE',
          headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
          success: function (res) {
            $('#staffTable').DataTable().ajax.reload();
            toastr.success("Staff deleted successfully!");

          },
          error: function (err) {
            console.error(err);
            toastr.error("Error deleting staff.");

          }
        });
      });


    </script>

  @endpush