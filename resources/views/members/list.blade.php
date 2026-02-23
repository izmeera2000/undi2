@extends('layouts.app')

@section('title', 'Members List')

@section('breadcrumb')
  @php
    $crumbs[] = ['label' => 'Member', 'url' => route('members.list')];

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



    @include('members.partials.list.modals')



@endsection




  @push('scripts')
    <script src="{{ asset('assets/vendors/datatables/datatables.js') }}"></script>

    <script>
      $(document).ready(function() {
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
              if (xhr.status == 419) {
                location.reload();
              }
            },
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




        const form = document.getElementById('addMemberForm');
        const submitBtn = document.getElementById('addMemberBtn');
        const avatarInput = document.getElementById('avatarUpload');
        const avatarPreview = document.getElementById('avatarPreview');

        // =========================
        // AVATAR PREVIEW
        // =========================
        avatarInput.addEventListener('change', (e) => {
          const file = e.target.files[0];
          if (!file) return;

          const reader = new FileReader();
          reader.onload = function (event) {
            avatarPreview.src = event.target.result;
          };
          reader.readAsDataURL(file);
        });

        // =========================
        // AJAX SUBMISSION
        // =========================
        submitBtn.addEventListener('click', (e) => {
          e.preventDefault();

          if (!form.reportValidity()) return;

          const formData = new FormData(form);

          submitBtn.disabled = true;
          submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>Adding...`;

          fetch("{{ route('members.store') }}", {
            method: "POST",
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
          })
            .then(res => {
              submitBtn.disabled = false;
              submitBtn.innerHTML = `<i class="bi bi-plus-lg me-1"></i> Add Member`;
              if (!res.ok) throw new Error('Network response was not ok');
              return res.json();
            })
            .then(data => {
              if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('addMemberModal')).hide();
                form.reset();
                avatarPreview.src = "{{ asset('assets/img/avatars/avatar-placeholder.webp') }}";

                // Optional: reload DataTable or members list
                if (typeof membersTable !== 'undefined') {
                  membersTable.ajax.reload();
                }

                Swal.fire({
                  icon: 'success',
                  title: 'Member added!',
                  showConfirmButton: false,
                  timer: 1500
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: data.message || 'Failed to add member'
                });
              }
            })
            .catch(err => {
              console.error(err);
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong'
              });
            });
        });




$('#dun_id').on('change', function () {
    var dunKod = $(this).val(); // this is now kod_dun
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Reset DM dropdown
    $('#kod_dm').html('<option value="">-- Select DM --</option>');

    if (dunKod) {
        $.ajax({
            url: "{{ route('members.duns', ':dunKod') }}".replace(':dunKod', dunKod),
            method: 'GET',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(response) {
                if (response.dms.length > 0) {
                    response.dms.forEach(function(dm) {
                        $('#kod_dm').append(
                            '<option value="' + dm.koddm + '">' + dm.namadm + ' (' + dm.koddm + ')</option>'
                        );
                    });
                } else {
                    $('#kod_dm').append('<option value="">No DM found for this DUN</option>');
                }
            },
            error: function() {
                alert('Error fetching DM data.');
            }
        });
    }
});






      });





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



      // When the DUN dropdown changes



    </script>

  @endpush