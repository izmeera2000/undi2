@extends('layouts.app')

@section('title', 'DM List')

@section('breadcrumb')
    @php
        $crumbs = [
            ['label' => 'DM', 'url' => route('dm.index')],
            ['label' => 'List', 'url' => route('dm.index')],
        ];
    @endphp
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables/datatables.css')}}">
@endpush

@section('content')
    <section class="section">
        <div class="card g-4 mb-4">
            <div class="card-header">
                <div class="row g-3 align-items-center w-100">
                    <div class="col-md-4 col-12">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" id="dmSearch" class="form-control border-start-0 ps-0"
                                placeholder="Search DM...">
                        </div>
                    </div>
                    <div class="col-md-8 col-12">
                        <div class="d-flex flex-wrap justify-content-md-end gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDMModal">
                                <i class="bi bi-plus-lg me-1"></i> Add DM
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-1">
                <div class="table-responsive">
                    <table id="dmTable" class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kod DM</th>
                                <th>Nama DM</th>
                                <th>DUN</th>
                                <th>Effective From</th>
                                <th>Effective To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- Add DM Modal --}}
    <div class="modal fade" id="addDMModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="addDMForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Add DM</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kod DM</label>
                            <input type="text" name="koddm" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama DM</label>
                            <input type="text" name="namadm" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">DUN</label>
                            <select name="dun_id" class="form-select" required>
                                @foreach($duns as $dun)
                                    <option value="{{ $dun->id }}">{{ $dun->namadun }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Effective From</label>
                            <input type="date" name="effective_from" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Effective To</label>
                            <input type="date" name="effective_to" class="form-control">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save DM</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('assets/vendors/datatables/datatables.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const table = $('#dmTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('dm.data') }}",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    error: function (xhr) {
                        // Handle 401 Unauthorized
                        if (xhr.status === 401) {
                            window.location.href = "{{ route('login') }}";  // Redirect to login page
                        }
                        // Handle 419 Page Expired
                        if (xhr.status === 419) {
                            location.reload();  // Reload the page
                        }
                    }
                },
                columns: [
                    { data: 'koddm', name: 'koddm' },
                    { data: 'namadm', name: 'namadm' },
                    { data: 'dun_name', name: 'dun' },
                    { data: 'effective_from', name: 'effective_from' },
                    { data: 'effective_to', name: 'effective_to' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ]
            });

            // Search
            $('#dmSearch').on('keyup', function () {
                table.search(this.value).draw();
            });

            // Add DM
            $('#addDMForm').submit(function (e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('dm.store') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')  // Ensure this meta tag is in your HTML <head>
                    },
                    success: function () {
                        $('#addDMModal').modal('hide');
                        table.ajax.reload();
                        $('#addDMForm')[0].reset();
                    },
                    error: function (xhr, status, error) {
                        alert('Error saving DM!');
                        console.error('Error:', error);
                        console.error('Response:', xhr.responseText);
                    }
                });

            });

            // Row click to go to show page
            $('#dmTable tbody').on('click', 'tr', function () {
                const dmId = table.row(this).data().id;  // Get the ID of the clicked row
                window.location.href = "{{ url('dm') }}/" + dmId;  // Redirect to the show page
            });

            // Delete DM
            $('#dmTable').on('click', '.delete-dm', function (e) {
                e.stopPropagation();  // Prevent triggering row click when clicking delete
                const dmId = $(this).data('id');

                if (confirm('Are you sure you want to delete this DM?')) {
                    $.ajax({
                        url: "{{ route('dm.destroy', ':id') }}".replace(':id', dmId), // dynamically insert ID
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: {
                            _method: 'DELETE' // Laravel expects DELETE
                        },
                        success: function () {
                            table.ajax.reload();
                            alert('DM deleted successfully.');
                        },
                        error: function (xhr) {
                            alert('Error deleting DM!');
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        });
    </script>
@endpush