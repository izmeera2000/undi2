@extends('layouts.app')

@section('title', 'Lokaliti List')

@section('breadcrumb')
    @php
        $crumbs = [
            ['label' => 'Lokaliti', 'url' => route('lokaliti.index')],
            ['label' => 'List', 'url' => route('lokaliti.index')],
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
                            <input type="text" id="lokalitiSearch" class="form-control border-start-0 ps-0"
                                placeholder="Search Lokaliti...">
                        </div>
                    </div>
                    <div class="col-md-8 col-12">
                        <div class="d-flex flex-wrap justify-content-md-end gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLokalitiModal">
                                <i class="bi bi-plus-lg me-1"></i> Add Lokaliti
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-1">
                <div class="table-responsive">
                    <table id="lokalitiTable" class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kod Lokaliti</th>
                                <th>Nama Lokaliti</th>
                                <th>DM</th>
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

    {{-- Add Lokaliti Modal --}}
    <div class="modal fade" id="addLokalitiModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="addLokalitiForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Lokaliti</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kod Lokaliti</label>
                            <input type="text" name="kod_lokaliti" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Lokaliti</label>
                            <input type="text" name="nama_lokaliti" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">DM</label>
                            <select name="dm_id" class="form-select" required>
                                @foreach($dms as $dm)
                                    <option value="{{ $dm->id }}">{{ $dm->namadm }}</option>
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
                        <button type="submit" class="btn btn-primary">Save Lokaliti</button>
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

            const table = $('#lokalitiTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('lokaliti.data') }}",
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
                    { data: 'kod_lokaliti', name: 'kod_lokaliti' },
                    { data: 'nama_lokaliti', name: 'nama_lokaliti' },
                    { data: 'dm_name', name: 'dm_name' },
                    { data: 'effective_from', name: 'effective_from' },
                    { data: 'effective_to', name: 'effective_to' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ]

            });

            // Search
            $('#lokalitiSearch').on('keyup', function () {
                table.search(this.value).draw();
            });

            // Add Lokaliti
            $('#addLokalitiForm').submit(function (e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('lokaliti.store') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function () {
                        $('#addLokalitiModal').modal('hide');
                        table.ajax.reload();
                        $('#addLokalitiForm')[0].reset();
                    },
                    error: function () {
                        alert('Error saving Lokaliti!');
                    }
                });
            });

            // Row click to go to show page
            $('#lokalitiTable tbody').on('click', 'tr', function () {
                const lokalitiId = table.row(this).data().id;  // Get the ID of the clicked row
                window.location.href = "{{ url('lokaliti') }}/" + lokalitiId;  // Redirect to the show page
            });

            // Delete Lokaliti
            $('#lokalitiTable').on('click', '.delete-lokaliti', function (e) {
                e.stopPropagation();  // Prevent triggering row click when clicking delete
                const lokalitiId = $(this).data('id');

                if (confirm('Are you sure you want to delete this Lokaliti?')) {
                    $.ajax({
                        url: "{{ route('lokaliti.destroy', ':id') }}".replace(':id', lokalitiId), // dynamically insert ID
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: {
                            _method: 'DELETE' // Laravel expects DELETE
                        },
                        success: function () {
                            table.ajax.reload();
                            alert('Lokaliti deleted successfully.');
                        },
                        error: function (xhr) {
                            alert('Error deleting Lokaliti!');
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        });
    </script>
@endpush