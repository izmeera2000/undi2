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

    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables/datatables.css')}}">

@endpush


@section('content')

    <div class="card">
        <div class="card-body">

            <div class="row  ">
                <div class="col-md-6">

                    <div class="mb-3">
                        <label for="pilihanRayaType" class="form-label">Jenis Pilihan Raya</label>
                        <select name="pilihan_raya_type" id="pilihanRayaType" class="form-select">
                            <option value="">-- Pilih Jenis --</option>
                            @foreach($pilihanRayaTypes as $type)
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
                            @foreach($pilihanRayaSeries as $series)
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
                        {{-- @foreach($parlimens as $par)
                        <option value="{{ $par->id }}">{{ $par->namapar }}</option>
                        @endforeach --}}
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">DUN</label>
                    <select id="dunSelect" class="form-select" disabled>
                        <option value="">-- Pilih DUN --</option>
                        {{-- @foreach($duns as $dun)
                        <option value="{{ $dun->kod_dun }}" data-parent="{{ $dun->parlimen_id }}">
                            {{ $dun->namadun }} ({{ $dun->kod_dun }})
                        </option>
                        @endforeach --}}
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">DM</label>
                    <select id="dmSelect" class="form-select" disabled>
                        <option value="">-- Pilih DM --</option>
                        {{-- @foreach($dms as $dm)
                        <option value="{{ $dm->koddm }}" data-parent="{{ $dm->kod_dun }}">{{ $dm->koddm }}</option>
                        @endforeach --}}
                    </select>
                </div>

                {{-- <div class="col-md-3">
                    <label class="form-label">Lokaliti</label>
                    <select id="lokalitiSelect" class="form-select" disabled>
                        <option value="">-- Pilih Lokaliti --</option>
                        @foreach($lokalitis as $loc)
                        <option value="{{ $loc->kod_lokaliti }}" data-parent="{{ $loc->dm->koddm }}">
                            {{ $loc->kod_lokaliti }}
                        </option>
                        @endforeach
                    </select>
                </div> --}}
                <div class="col-md-3">

                    <button id="downloadPdfBtn" class="btn btn-primary" style="display:none;">Download PDF</button>
                </div>

            </div>



            <table id="pengundiTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th rowspan="2" style="display:none;">Kod Lokaliti</th>
                        <th rowspan="2">Lokaliti</th>
                        <th colspan="7" class="text-center">Saluran</th>
                        <th rowspan="2">Total</th>
                    </tr>
                    <tr>
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>4</th>
                        <th>5</th>
                        <th>6</th>
                        <th>7</th>
                    </tr>
                </thead>
            </table>


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

                resetDropdowns();
                pruHierarchy = {};

                if (!typeSelect.value || !seriesSelect.value) return;

                const res = await fetch(
                    `{{ route('pengundi.ajax.pru.hierarchy') }}?type=${typeSelect.value}&series=${seriesSelect.value}`
                );

                const data = await res.json();
                if (!data.length) return;

                data.forEach(row => {

                    if (!pruHierarchy[row.parlimen_id])
                        pruHierarchy[row.parlimen_id] = { namapar: row.namapar, duns: {} };

                    if (!pruHierarchy[row.parlimen_id].duns[row.kod_dun])
                        pruHierarchy[row.parlimen_id].duns[row.kod_dun] = { namadun: row.namadun, dms: {} };

                    if (!pruHierarchy[row.parlimen_id].duns[row.kod_dun].dms[row.koddm])
                        pruHierarchy[row.parlimen_id].duns[row.kod_dun].dms[row.koddm] = { namadm: row.namadm };

                });

                buildParlimen();
            }

            function buildParlimen() {
                parSelect.disabled = false;
                for (const id in pruHierarchy) {
                    parSelect.innerHTML += `<option value="${id}">${pruHierarchy[id].namapar}</option>`;
                }
            }

            function buildDun(parId) {
                dunSelect.innerHTML = '<option value="">-- Pilih DUN --</option>';
                dmSelect.innerHTML = '<option value="">-- Pilih DM --</option>';

                if (!parId || !pruHierarchy[parId]) return;

                const duns = pruHierarchy[parId].duns;

                for (const kod in duns) {
                    dunSelect.innerHTML += `<option value="${kod}">${duns[kod].namadun}</option>`;
                }

                dunSelect.disabled = false;
                dmSelect.disabled = true;
            }

            function buildDm(parId, dunId) {
                dmSelect.innerHTML = '<option value="">-- Pilih DM --</option>';

                if (!dunId || !pruHierarchy[parId]?.duns[dunId]) return;

                const dms = pruHierarchy[parId].duns[dunId].dms;

                for (const kod in dms) {
                    dmSelect.innerHTML += `<option value="${kod}">${dms[kod].namadm}</option>`;
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
                const downloadBtn = document.getElementById('downloadPdfBtn');
                if (downloadBtn) downloadBtn.style.display = 'none'; // hide initially

                table = $('#pengundiTable').DataTable({
                    processing: true,
                    serverSide: false,
                    stateSave: true,
                    deferLoading: 0,
                    searching: true,
                    paging: true,
                    fixedHeader: true,
                    orderCellsTop: true,

                    columns: [
                        { data: 'kod_lokaliti', visible: false, defaultContent: '' },
                        { data: 'nama_lokaliti', defaultContent: '' },
                        { data: 'saluran_1', defaultContent: 0, render: renderSaluranLink },
                        { data: 'saluran_2', defaultContent: 0, render: renderSaluranLink },
                        { data: 'saluran_3', defaultContent: 0, render: renderSaluranLink },
                        { data: 'saluran_4', defaultContent: 0, render: renderSaluranLink },
                        { data: 'saluran_5', defaultContent: 0, render: renderSaluranLink },
                        { data: 'saluran_6', defaultContent: 0, render: renderSaluranLink },
                        { data: 'saluran_7', defaultContent: 0, render: renderSaluranLink },
                        { data: 'total', defaultContent: 0 }
                    ],

                    ajax: {
                        url: "{{ route('pengundi.list_data') }}",
                        type: "POST",
                        data: function (d) {
                            if (!allFiltersSelected()) {
                                return {}; // send empty object, backend handles
                            }

                            d.parlimen = parSelect.value;
                            d.dun = dunSelect.value;
                            d.dm = dmSelect.value;
                            d.type = typeSelect.value;
                            d.series = seriesSelect.value;
                        },
                        dataSrc: function (json) {
                            // Show or hide download button based on returned data

                            console.log(json);
                            if (downloadBtn) {
                                if (json.data && json.data.length > 0) {
                                    downloadBtn.style.display = 'inline-block'; // show button
                                } else {
                                    downloadBtn.style.display = 'none'; // hide button
                                }
                            }
                            return json.data ?? [];
                        }
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

document.getElementById('downloadPdfBtn').addEventListener('click', function () {

    const data = {
        parlimen: document.getElementById('parlimenSelect').value,
        dun: document.getElementById('dunSelect').value,
        dm: document.getElementById('dmSelect').value,
        type: document.getElementById('pilihanRayaType').value,
        series: document.getElementById('pilihanRayaSeries').value,
    };

    fetch('{{ route("pengundi.list_data_pdf") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(response => {

        if (response.success) {
            toastr.success(response.message);
        } else {
            toastr.error(response.message);
        }

    })
    .catch(error => {
        toastr.error('Something went wrong.');
    });

});

        });
    </script>

@endpush