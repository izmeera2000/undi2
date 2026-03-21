@extends('layouts.app')

@section('title', 'Pengundi List')

@section('breadcrumb')
    @php
        $crumbs = [
            ['label' => 'Pengundi'],
            ['label' => 'List', 'url' => route('pengundi.list')],
        ];
    @endphp
@endsection



@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables/datatables.css') }}">


@endpush


@section('content')

    <div class="card">

        <div class="card-header">
            <div class="row g-3 align-items-center w-100">
                <div class="d-flex flex-wrap flex-md-row justify-content-md-end gap-2 align-items-center">

                    <!-- History Button -->
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#activityModal">
                        <i class="bi bi-clock-history me-1"></i> History
                    </button>

                    <!-- Analytics Button -->
                    <a href="{{ route('pengundi.bulkimport2') }}" class="btn btn-success">
                        <i class="bi bi-upload me-1"></i> Bulk Import
                    </a>

                </div>
            </div>
        </div>

        <div class="card-body">

            <div class="row  ">
                <div class="col-md-6">

                    <div class="mb-3">
                        <label for="pilihanRayaType" class="form-label">Jenis Pilihan Raya</label>
                        <select name="pilihan_raya_type" id="pilihanRayaType" class="form-select">
                            <option value="">-- Pilih Jenis --</option>
                            @foreach($electionType as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">

                    <div class="mb-3">
                        <label for="pilihanRayaSeries" class="form-label">Series Pilihan Raya</label>
                        <select name="pilihan_raya_series" id="pilihanRayaSeries" class="form-select">
                            <option value="">-- Pilih Series --</option>
                            @foreach($electionNumbers as $series)
                                <option value="{{ $series }}">{{ $series }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label">Parlimen</label>
                    <select id="parlimenSelect" class="form-select">
                        <option value="">-- Pilih Parlimen --</option>

                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">DUN</label>
                    <select id="dunSelect" class="form-select" disabled>
                        <option value="">-- Pilih DUN --</option>

                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">DM</label>
                    <select id="dmSelect" class="form-select" disabled>
                        <option value="">-- Pilih DM --</option>

                    </select>
                </div>

                <div class="col-md-3 d-flex flex-column justify-content-end">
                    <div class="d-flex justify-content-md-end align-items-md-end">
                        <div class="btn-group w-100 w-lg-auto" id="pdfButtonGroup" style="display:none;">

                            <!-- Main Action -->
                            <button id="viewPdfBtn" type="button" class="btn btn-primary w-100  ">
                                View PDF
                            </button>

                            <!-- Split Dropdown Toggle -->
                            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>

                            <!-- Dropdown Menu -->
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="#" id="generatePdfBtn">
                                        Generate PDF
                                    </a>
                                </li>
                            </ul>

                        </div>
                    </div>

                </div>
            </div>

            <div class="table-responsive">
                <table id="pengundiTable" class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th rowspan="2" style="display:none;">Kod Lokaliti</th>
                            <th rowspan="2">Lokaliti</th>
                            <th colspan="{{ $saluranList->count() }}" class="text-center">Saluran</th>
                            <th rowspan="2">Total</th>
                        </tr>
                        <tr>
                            @foreach ($saluranList as $saluran)
                                <th>{{ $saluran }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th style="display:none;"></th> <!-- Kod Lokaliti -->
                            <th></th> <!-- Lokaliti -->
                            @foreach ($saluranList as $saluran)
                                <th></th> <!-- Saluran {{ $saluran }} -->
                            @endforeach
                            <th></th> <!-- Total -->
                        </tr>
                    </tfoot>
                </table>
            </div>


        </div>
    </div>

    <!-- View Files Modal -->
    <div class="modal fade" id="pdfListModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Available PDF Files</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="pdfFileList">
                        <div class="text-muted">Loading...</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <div id="pdfFileListfooter">
                        <button type="button" class="btn btn-primary m-2" id="generatePdfBtn2">
                            Generate PDF
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>



    <div class="modal fade" id="activityModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="table-responsive">

                        <table id="activityTable" class="table table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection


@push('scripts')
    <script src="{{ asset('assets/vendors/datatables/datatables.js') }}"></script>

    <script>
        $(document).ready(function () {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            let activityTable;

            $('#activityModal').on('shown.bs.modal', function () {

                if (!activityTable) {

                    activityTable = $('#activityTable').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: "{{ route('pengundi.activity') }}",

                        columns: [
                            { data: 'created_at', name: 'created_at' },
                            { data: 'user', name: 'user' },
                            { data: 'action', name: 'action' }
                        ]
                    });

                } else {
                    activityTable.ajax.reload();
                }

            });

            const parSelect = document.getElementById('parlimenSelect');
            const dunSelect = document.getElementById('dunSelect');
            const dmSelect = document.getElementById('dmSelect');
            const typeSelect = document.getElementById('pilihanRayaType');
            const seriesSelect = document.getElementById('pilihanRayaSeries');

            let table;
            let pruHierarchy = {};

            // =====================================================
            // HELPER: CHECK ALL FILTERS SELECTED
            // =====================================================
            function allFiltersSelected() {
                return (
                    typeSelect.value &&
                    seriesSelect.value &&
                    parSelect.value &&
                    dunSelect.value &&
                    dmSelect.value
                );
            }

            // =====================================================
            // RESET DROPDOWNS
            // =====================================================
            function resetDropdowns() {
                parSelect.innerHTML = '<option value="">-- Pilih Parlimen --</option>';
                dunSelect.innerHTML = '<option value="">-- Pilih DUN --</option>';
                dmSelect.innerHTML = '<option value="">-- Pilih DM --</option>';

                parSelect.disabled = true;
                dunSelect.disabled = true;
                dmSelect.disabled = true;
            }

            // =====================================================
            // LOAD HIERARCHY
            // =====================================================
            async function loadHierarchy() {

                console.log('loadHierarchy triggered', {
                    type: typeSelect.value,
                    series: seriesSelect.value
                });

                resetDropdowns();
                pruHierarchy = {};

                if (!typeSelect.value || !seriesSelect.value) {
                    console.warn('Missing type or series', {
                        type: typeSelect.value,
                        series: seriesSelect.value
                    });
                    return;
                }

                const url = `{{ route('pengundi.ajax.pru.hierarchy') }}?type=${typeSelect.value}&series=${seriesSelect.value}`;

                console.log('Fetching hierarchy from:', url);

                try {
                    const res = await fetch(url);
                    console.log('Response status:', res.status);

                    const result = await res.json();
                    console.log('Response data:', result);

                    // Extract hierarchy and saluran list
                    const data = result.hierarchy || [];
                    const saluranList = result.saluran_list || [];

                    if (!data.length) {
                        console.warn('Empty hierarchy data');
                        return;
                    }

                    data.forEach((row, index) => {

                        // console.log(`Processing row ${index}`, row);

                        if (!pruHierarchy[row.kod_par]) {
                            pruHierarchy[row.kod_par] = {
                                nama_par: row.nama_par,
                                duns: {}
                            };
                        }

                        if (!pruHierarchy[row.kod_par].duns[row.kod_dun]) {
                            pruHierarchy[row.kod_par].duns[row.kod_dun] = {
                                nama_dun: row.nama_dun,
                                dms: {}
                            };
                        }

                        if (!pruHierarchy[row.kod_par].duns[row.kod_dun].dms[row.kod_dm]) {
                            pruHierarchy[row.kod_par].duns[row.kod_dun].dms[row.kod_dm] = {
                                nama_dm: row.nama_dm
                            };
                        }

                    });

                    console.log('Final hierarchy object:', pruHierarchy);

                    buildParlimen();

                } catch (error) {
                    console.error('Error loading hierarchy:', error);
                }
            }

            function buildParlimen() {
                parSelect.disabled = false;
                for (const id in pruHierarchy) {
                    parSelect.innerHTML += `<option value="${id}">${pruHierarchy[id].nama_par}</option>`;
                }
            }

            function buildDun(parId) {
                dunSelect.innerHTML = '<option value="">-- Pilih DUN --</option>';
                dmSelect.innerHTML = '<option value="">-- Pilih DM --</option>';

                if (!parId || !pruHierarchy[parId]) return;

                const duns = pruHierarchy[parId].duns;

                for (const kod in duns) {
                    dunSelect.innerHTML += `<option value="${kod}">${duns[kod].nama_dun}</option>`;
                }

                dunSelect.disabled = false;
                dmSelect.disabled = true;
            }

            function buildDm(parId, dunId) {
                dmSelect.innerHTML = '<option value="">-- Pilih DM --</option>';

                if (!dunId || !pruHierarchy[parId]?.duns[dunId]) return;

                const dms = pruHierarchy[parId].duns[dunId].dms;

                for (const kod in dms) {
                    dmSelect.innerHTML += `<option value="${kod}">${dms[kod].nama_dm}</option>`;
                }

                dmSelect.disabled = false;
            }

            function renderSaluranLink(data, type, row, meta) {

                if (!data || data == 0) return 0;

                const columnName = meta.settings.aoColumns[meta.col].data;

                if (row['link_' + columnName]) {
                    return `<a href="${row['link_' + columnName]}">${data}</a>`;
                }

                return data;
            }

            // =====================================================
            // INIT DATATABLE
            // =====================================================
            function initDataTable() {

                // Generate saluran columns dynamically from Blade
                let saluranColumns = [
                    @foreach ($saluranList as $index => $saluran)
                        { data: 'saluran_{{ $saluran }}', defaultContent: 0, render: renderSaluranLink }{{ !$loop->last ? ',' : '' }}
                    @endforeach
                                        ];

                // Full columns array including kod_lokaliti, nama_lokaliti, saluran columns, and total
                let columnsArray = [
                    { data: 'kod_lokaliti', visible: false, defaultContent: '' },
                    { data: 'nama_lokaliti', defaultContent: '' },
                    ...saluranColumns,
                    { data: 'total', defaultContent: 0 }
                ];

                table = $('#pengundiTable').DataTable({
                    processing: true,
                    serverSide: false,
                    stateSave: true,
                    deferLoading: 0,
                    searching: true,
                    paging: true,
                    fixedHeader: true,
                    orderCellsTop: true,

                    columns: columnsArray,

                    ajax: {
                        url: "{{ route('pengundi.list_data') }}",
                        type: "POST",
                        data: function (d) {
                            if (!allFiltersSelected()) return {};

                            d.parlimen = parSelect.value;
                            d.dun = dunSelect.value;
                            d.dm = dmSelect.value;
                            d.type = typeSelect.value;
                            d.series = seriesSelect.value;
                        },
                        dataSrc: function (json) {
                            const isEmpty = !Array.isArray(json.data) || json.data.length === 0;

                            checkPdfStatus(isEmpty);
                            return json.data ?? [];
                        }
                    },

                    footerCallback: function (row, data, start, end, display) {
                        let api = this.api();

                        // Sum the "Total" column (last column)
                        const totalSum = api
                            .column(api.columns().count() - 1, { page: 'current' })
                            .data()
                            .reduce((a, b) => a + (parseInt(b) || 0), 0);

                        // Clear footer cells
                        for (let i = 0; i < api.columns().count() - 1; i++) {
                            $(api.column(i).footer()).html('');
                        }

                        // Set only the last footer cell
                        $(api.column(api.columns().count() - 1).footer()).html(totalSum);
                    },

                    stateSaveParams: function (settings, data) {
                        data.filters = {
                            type: typeSelect.value,
                            series: seriesSelect.value,
                            par: parSelect.value,
                            dun: dunSelect.value,
                            dm: dmSelect.value
                        };
                    },

                    language: {
                        emptyTable: "Sila pilih semua filter untuk lihat data"
                    }
                });

            }

            // =====================================================
            // FULL RESTORE FLOW
            // =====================================================
            (async function () {

                resetDropdowns();

                let savedState = JSON.parse(
                    localStorage.getItem('DataTables_pengundiTable_' + window.location.pathname)
                );

                if (savedState?.filters) {

                    typeSelect.value = savedState.filters.type || "";
                    seriesSelect.value = savedState.filters.series || "";

                    if (typeSelect.value && seriesSelect.value) {

                        await loadHierarchy();

                        if (savedState.filters.par) {
                            parSelect.value = savedState.filters.par;
                            buildDun(parSelect.value);
                        }

                        if (savedState.filters.dun) {
                            dunSelect.value = savedState.filters.dun;
                            buildDm(parSelect.value, dunSelect.value);
                        }

                        if (savedState.filters.dm) {
                            dmSelect.value = savedState.filters.dm;
                        }
                    }
                }

                initDataTable();

                if (allFiltersSelected()) {
                    table.ajax.reload();
                }

            })();

            // =====================================================
            // EVENTS
            // =====================================================
            typeSelect.addEventListener('change', async function () {
                await loadHierarchy();
                if (allFiltersSelected()) table.ajax.reload();
            });

            seriesSelect.addEventListener('change', async function () {
                await loadHierarchy();
                if (allFiltersSelected()) table.ajax.reload();
            });

            parSelect.addEventListener('change', function () {
                buildDun(this.value);
                if (allFiltersSelected()) table.ajax.reload();
            });

            dunSelect.addEventListener('change', function () {
                buildDm(parSelect.value, this.value);
                if (allFiltersSelected()) table.ajax.reload();
            });

            dmSelect.addEventListener('change', function () {
                if (allFiltersSelected()) table.ajax.reload();
            });

            document.getElementById('generatePdfBtn').addEventListener('click', function () {

                genPDF();
            });

            document.getElementById('generatePdfBtn2').addEventListener('click', function () {
                // console.log("test");
                genPDF();
            });



        });



        function checkPdfStatus(isEmpty) {

            const data = {
                parlimen: document.getElementById('parlimenSelect').value,
                dun: document.getElementById('dunSelect').value,
                dm: document.getElementById('dmSelect').value,
                type: document.getElementById('pilihanRayaType').value,
                series: document.getElementById('pilihanRayaSeries').value,
            };

            fetch('{{ route("pengundi.list.check_pdf") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(res => res.json())
                .then(response => {

                    if (isEmpty) {
                        pdfButtonGroup.style.display = 'none'; // hide
                    } else {
                        pdfButtonGroup.style.display = 'inline-flex'; // show

                    }


                    document.getElementById('viewPdfBtn').addEventListener('click', function (e) {
                        e.preventDefault();
                        openPdfModal();
                    });




                });
        }





        async function genPDF() {
            // Cache DOM elements
            const parlimen = document.getElementById('parlimenSelect').value;
            const dun = document.getElementById('dunSelect').value;
            const dm = document.getElementById('dmSelect').value;
            const type = document.getElementById('pilihanRayaType').value;
            const series = document.getElementById('pilihanRayaSeries').value;
            const btn = document.getElementById('generatePdfBtn2');
            const pdfModalEl = document.getElementById('pdfListModal');

            // Disable button to prevent multiple clicks
            if (btn) btn.disabled = true;

            // Hide the PDF modal
            if (pdfModalEl) {
                const modalInstance = bootstrap.Modal.getInstance(pdfModalEl);
                if (modalInstance) modalInstance.hide();
            }

            const data = { parlimen, dun, dm, type, series };

            try {
                const res = await fetch('{{ route("pengundi.list_data_pdf") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const response = await res.json();

                if (response.success) {
                    toastr.success(response.message);



                } else {
                    toastr.error(response.message);
                }

            } catch (error) {
                console.error(error);
                toastr.error('Something went wrong while generating PDF.');
            } finally {
                // Re-enable button
                if (btn) btn.disabled = false;
            }
        }



        function openPdfModal() {
            const modalEl = document.getElementById('pdfListModal');
            const modal = new bootstrap.Modal(modalEl);

            // Cache DOM elements
            const pdfListEl = document.getElementById('pdfFileList');
            const pdfFooterEl = document.getElementById('pdfFileListfooter');

            // Prepare data
            const data = {
                parlimen: document.getElementById('parlimenSelect').value,
                dun: document.getElementById('dunSelect').value,
                dm: document.getElementById('dmSelect').value,
                type: document.getElementById('pilihanRayaType').value,
                series: document.getElementById('pilihanRayaSeries').value,
            };

            fetch('{{ route("pengundi.list.check_pdf") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
                .then(res => res.json())
                .then(response => {
                    // Build PDF list HTML
                    const pdfListHtml = (!response.exists || !response.files.length)
                        ? `<div class="text-danger">No PDF files found.<br></div>`
                        : `<div class="list-group">
                        ${response.files.map(file => `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${file.name}</strong><br>
                                    <small class="text-muted">${file.last_modified}</small>
                                </div>
                                <div>
                                    <a href="${file.url}" target="_blank" class="btn btn-sm btn-info">View</a>
                                    <a href="${file.url}" download class="btn btn-sm btn-success">Download</a>
                                </div>
                            </div>
                        `).join('')}
                    </div>`;



                    // Insert into DOM
                    pdfListEl.innerHTML = pdfListHtml;

                    // Show modal
                    modal.show();
                })
                .catch(err => {
                    console.error(err);
                    pdfListEl.innerHTML = `<div class="text-danger">Failed to load PDF files.</div>`;
                    modal.show();
                });
        }



    </script>

@endpush