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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable@13.0.0/dist/handsontable.full.min.css">
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
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkLokalitiModal">
                                <i class="bi bi-table me-1"></i> Bulk Add (Excel Style)
                            </button>
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

                        {{-- 1️⃣ DM Selection at Top --}}
                        <div class="mb-3">
                            <label class="form-label">DM</label>
                            <select name="koddm" id="koddm" class="form-select" required>
                                @foreach($dms->unique('koddm') as $dm)
                                    <option value="{{ $dm->koddm }}">
                                        {{ $dm->koddm }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 2️⃣ Kod Lokaliti (3 digits only) --}}
                        <div class="mb-3">
                            <label class="form-label">Kod Lokaliti (3 digits)</label>
                            <input type="text" name="kod_lokaliti" id="kod_lokaliti" class="form-control" maxlength="3"
                                pattern="\d{3}" required>
                            <small class="text-muted">Enter 3 digits only. Full code will be generated
                                automatically.</small>
                        </div>

                        {{-- 3️⃣ Full Kod Lokaliti (readonly, auto-generated) --}}


                        {{-- 4️⃣ Nama Lokaliti --}}
                        <div class="mb-3">
                            <label class="form-label">Nama Lokaliti</label>
                            <input type="text" name="nama_lokaliti" class="form-control" required>
                        </div>

                        {{-- 5️⃣ Effective Dates --}}
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
    {{-- Bulk Lokaliti Modal --}}
    <div class="modal fade" id="bulkLokalitiModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Bulk Add Lokaliti</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="lokalitiHotTable" style="height:65vh;"></div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button id="saveBulkLokaliti" class="btn btn-success">Save All</button>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('assets/vendors/datatables/datatables.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/handsontable@13.0.0/dist/handsontable.full.min.js"></script>

    <script>
        $(document).ready(function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');







            let lokalitiHot;

            $('#bulkLokalitiModal').on('shown.bs.modal', function () {

                const container = document.getElementById('lokalitiHotTable');

                if (!lokalitiHot) {

                    lokalitiHot = new Handsontable(container, {
                        data: [],
                        rowHeaders: true,
                        contextMenu: true,
                        stretchH: 'none',
                        minSpareRows: 1,
                        colWidths: [120, 200, 150, 150, 150],

                        colHeaders: [
                            'koddm',
                            'kod_lokaliti (3 digits)',
                            'nama_lokaliti',
                            'effective_from',
                            'effective_to'
                        ],

                        columns: [
                            {
                                type: 'dropdown',
                                source: @json($dms->unique('koddm')->pluck('koddm')->values())
                            },
                            { type: 'text' },
                            { type: 'text' },
                            { type: 'date', dateFormat: 'YYYY-MM-DD' },
                            { type: 'date', dateFormat: 'YYYY-MM-DD' }
                        ],

                        licenseKey: 'non-commercial-and-evaluation'
                    });
                }

                lokalitiHot.render();
                lokalitiHot.refreshDimensions();
            });


            $('#saveBulkLokaliti').click(function () {

    let data = lokalitiHot.getData();

    let filtered = data.filter(row =>
        row[0] && row[1] && row[2]
    );

    $.ajax({
        url: "{{ route('lokaliti.bulkStore') }}",
        method: "POST",
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        data: {
            data: filtered
        },
        success: function () {
            $('#bulkLokalitiModal').modal('hide');
            table.ajax.reload();
            alert('Bulk Lokaliti saved successfully!');
        },
        error: function (xhr) {
            alert('Error saving bulk Lokaliti!');
            console.error(xhr.responseText);
        }
    });
});




            const table = $('#lokalitiTable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
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
                        // $('#addLokalitiForm')[0].reset();
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