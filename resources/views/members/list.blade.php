@extends('layouts.app')

@section('title', 'Members List')

@section('breadcrumb')
  @php
    // Build dynamic crumbs based on request
    $crumbs = [
      ['label' => 'Members', 'url' => route('members.list')],
      ['label' => 'List', 'url' => route('members.list')],
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

      <!-- Member Table -->
      <div class="card g-4 mb-4">
        <div class="card-header">
          <div class="row g-3 align-items-center w-100">
            <div class="col-md-4 col-12">
              <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0">
                  <i class="bi bi-search text-muted"></i>
                </span>
                <input type="text" id="membersSearch" class="form-control border-start-0 ps-0"
                  placeholder="Search members...">
              </div>
            </div>
            <div class="col-md-8  col-12">
              <div class="d-flex flex-wrap justify-content-md-end gap-2">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                  <i class="bi bi-plus-lg me-1"></i> Add Member
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body p-0">
          <div class="table-responsive">
            <table id="membersTable" class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th class="ps-4" style="width: 40px;">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="selectAllMember">
                    </div>
                  </th>
                  <th>Member</th>
                  <th>Groups</th>
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



    <!-- Add Member Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header border-0 pb-0">
            <div>
              <h5 class="modal-title">Add New Member</h5>
              <p class="text-muted small mb-0">Member will set their password on first login</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <form id="addMemberForm">
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
            <button type="submit" class="btn btn-primary" id="addMemberBtn">
              <i class="bi bi-plus-lg me-1"></i> Add Member
            </button>
          </div>
        </div>
      </div>
    </div>




@endsection




  @push('scripts')
    <script src="{{ asset('assets/vendors/datatables/datatables.js') }}"></script>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const table = $('#membersTable').DataTable({
          processing: true,
          serverSide: true,
          ajax: {
            url: "{{ route('members.data') }}",
            type: "POST",
            headers: {
              'X-CSRF-TOKEN': csrfToken
            },
            error: function (xhr) {
              if (xhr.status === 401) {
                window.location.href = "{{ route('login') }}";
              }
            }
          },
          columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'members', name: 'nama' },      // 👈 fix this (see below)
            { data: 'groups', name: 'groups' },
            { data: 'joined', name: 'created_at' },
            { data: 'actions', orderable: false, searchable: false }
          ],
          searching: true,
          lengthChange: true,
          pageLength: 10
        });

        $('#membersSearch').on('keydown', function (e) {
          if (e.key === "Enter") {
            table.search(this.value).draw();
          }
        });

      });


      // $('#addMemberBtn').on('click', function (e) {
      //   e.preventDefault();

      //   let form = $('#addMemberForm')[0];
      //   let formData = new FormData(form);

      //   $.ajax({
      //     url: " ",
      //     method: "POST",
      //     data: formData,
      //     processData: false,
      //     contentType: false,
      //     headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      //     success: function (res) {
      //       $('#addMemberModal').modal('hide');
      //       $('#membersTable').DataTable().ajax.reload();
      //       toast.success("Member added successfully! They will set their password on first login.");

      //     },
      //     error: function (err) {
      //       console.log(err);
      //       toast.error("Error adding members.");

      //     }
      //   });
      // });



      // // Delete members via AJAX
      // $('#membersTable').on('click', '.delete-members-btn', function () {
      //   let membersId = $(this).data('id');

      //   if (!confirm('Are you sure you want to delete this members?')) return;

      //   $.ajax({
      //     url: `/members/${membersId}`, // matches Route::resource('members')
      //     type: 'DELETE',
      //     headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
      //     success: function (res) {
      //       $('#membersTable').DataTable().ajax.reload();
      //       toast.success("Member deleted successfully!");

      //     },
      //     error: function (err) {
      //       console.error(err);
      //       toast.error("Error deleting members.");

      //     }
      //   });
      // });


    </script>

  @endpush