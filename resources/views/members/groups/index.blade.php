{{-- resources/views/members/groups/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Groups List')

@section('breadcrumb')
  @php
    $crumbs[] = ['label' => 'Members', 'url' => route('members.list')];
    $crumbs[] = ['label' => 'Groups'];
  @endphp
@endsection

@push('styles')
  <link rel="stylesheet" href="{{ asset('assets/vendors/datatables/datatables.css')}}">
@endpush

@section('content')
<section class="section">
  <div class="card g-4 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Groups</h5>
      <a href="{{ route('members.groups.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Create Group
      </a>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table id="groupsTable" class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Group Name</th>
              <th>Description</th>
              <th>Members</th>
              <th class="text-end pe-4" style="width: 100px;">Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</section>

{{-- Invite/Remove Modals can go here --}}
@endsection

@push('scripts')
<script src="{{ asset('assets/vendors/datatables/datatables.js') }}"></script>
<script>
$(document).ready(function() {
  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  const table = $('#groupsTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "{{ route('members.groups.index') }}",
      type: "GET",
      headers: { 'X-CSRF-TOKEN': csrfToken },
      error: function(xhr) {
        if(xhr.status === 401) window.location.href = "{{ route('login') }}";
        if(xhr.status === 419) location.reload();
      }
    },
    columns: [
      { data: 'name', name: 'name' },
      { data: 'description', name: 'description' },
      { data: 'members', name: 'members', orderable: false, searchable: false },
      { data: 'actions', name: 'actions', orderable: false, searchable: false },
    ],
    pageLength: 10,
    lengthChange: true,
  });

  // Optional: search input
  $('#groupsSearch').on('keydown', function(e) {
    if(e.key === "Enter") table.search(this.value).draw();
  });
});
</script>
@endpush