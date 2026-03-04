@extends('layouts.app')

@section('title', 'Election List')

@section('breadcrumb')
    @php
        $crumbs = [
            ['label' => 'Elections', 'url' => route('elections.index')],
            ['label' => 'List', 'url' => route('elections.index')],
        ];
    @endphp
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables/datatables.css') }}">
    <style>
        #electionTable tbody tr { cursor: pointer; }
    </style>
@endpush

@section('content')
    <section class="section">
        <div class="card g-4 mb-4">
            <div class="card-header">
                <div class="row g-3 align-items-center w-100">
            
                    <div class="col-md-8 col-12">
                        <div class="d-flex flex-wrap justify-content-md-end gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addElectionModal">
                                <i class="bi bi-plus-lg me-1"></i> Add Election
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-1">
                <div class="table-responsive">
                    <table id="electionTable" class="table table-hover align-middle mb-0 w-100">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Number</th>
                                <th>Year</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    {{-- Add Election Modal --}}
    <div class="modal fade" id="addElectionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="addElectionForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Election</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Election Type</label>
                            <select name="type" class="form-select" required>
                                <option value="" selected disabled>Select Type</option>
                                <option value="PRU">PRU (General)</option>
                                <option value="PRN">PRN (State)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Election Number</label>
                            <input type="text" name="number" class="form-control" placeholder="e.g. 15" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control" value="{{ date('Y') }}" required>
                        </div>
                   
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Election</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendors/datatables/datatables.js') }}"></script>

    <script>
        $(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            const table = $('#electionTable').DataTable({
                processing: true,
                serverSide: true,
                    searching: false,
                 ajax: {
                    url: "{{ route('elections.data') }}", // Ensure this route exists in web.php
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                },
                columns: [
                    { 
                        data: 'type', 
                        render: function(data) {
                            let badgeClass = data === 'PRU' ? 'bg-primary' : 'bg-info text-dark';
                            return `<span class="badge ${badgeClass}">${data}</span>`;
                        }
                    },
                    { data: 'number', name: 'number' },
                    { data: 'year', name: 'year' },
                    { data: 'actions', orderable: false, searchable: false }
                ]
            });

            // Custom Search
            $('#electionSearch').on('keyup', function () {
                table.search(this.value).draw();
            });

            // Ajax Store
            $('#addElectionForm').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('elections.store') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function () {
                        $('#addElectionModal').modal('hide');
                        $('#addElectionForm')[0].reset();
                        table.ajax.reload();
                        toastr.success('Election added successfully!');
                    },
                    error: function () {
                        toastr.error('Error saving election!');
                    }
                });
            });

            // Row Click
            $('#electionTable tbody').on('click', 'tr', function (e) {
                if ($(e.target).closest('.btn-group').length) return;
                const data = table.row(this).data();
                window.location.href = "{{ url('elections') }}/" + data.id;
            });

            // Delete Action
            $('#electionTable').on('click', '.delete-election', function (e) {
                e.stopPropagation();
                const id = $(this).data('id');
                if (confirm('Delete this election record?')) {
                    $.ajax({
                        url: "{{ url('elections') }}/" + id,
                        method: 'POST',
                        data: { _method: 'DELETE', _token: csrfToken },
                        success: function () {
                            table.ajax.reload();
                            toastr.success('Deleted successfully.');
                        }
                    });
                }
            });
        });
    </script>
@endpush