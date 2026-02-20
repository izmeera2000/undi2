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

            let table;

            const parSelect = document.getElementById('parlimenSelect');
            const dunSelect = document.getElementById('dunSelect');
            const dmSelect = document.getElementById('dmSelect');
            const typeSelect = document.getElementById('pilihanRayaType');
            const seriesSelect = document.getElementById('pilihanRayaSeries');

            // ==============================
            // LOAD HIERARCHY
            // ==============================
            async function loadHierarchy() {

                if (!typeSelect.value || !seriesSelect.value) return;

                const res = await fetch(
                    `{{ route('pengundi.ajax.pru.hierarchy') }}?type=${typeSelect.value}&series=${seriesSelect.value}`
                );

                const data = await res.json();

                const hierarchy = {};

                data.forEach(row => {

                    if (!hierarchy[row.parlimen_id])
                        hierarchy[row.parlimen_id] = { namapar: row.namapar, duns: {} };

                    if (!hierarchy[row.parlimen_id].duns[row.kod_dun])
                        hierarchy[row.parlimen_id].duns[row.kod_dun] = { namadun: row.namadun, dms: {} };

                    if (!hierarchy[row.parlimen_id].duns[row.kod_dun].dms[row.koddm])
                        hierarchy[row.parlimen_id].duns[row.kod_dun].dms[row.koddm] = { namadm: row.namadm };

                });

                window.pruHierarchy = hierarchy;

                buildParlimen();
            }

            function buildParlimen() {
                parSelect.innerHTML = '<option value="">-- Pilih Parlimen --</option>';
                for (const id in window.pruHierarchy) {
                    parSelect.innerHTML += `<option value="${id}">${window.pruHierarchy[id].namapar}</option>`;
                }
                parSelect.disabled = false;
            }

            function buildDun(parId) {
                dunSelect.innerHTML = '<option value="">-- Pilih DUN --</option>';
                if (!parId) return;

                const duns = window.pruHierarchy[parId].duns;

                for (const kod in duns) {
                    dunSelect.innerHTML += `<option value="${kod}">${duns[kod].namadun}</option>`;
                }

                dunSelect.disabled = false;
            }

            function buildDm(parId, dunId) {
                dmSelect.innerHTML = '<option value="">-- Pilih DM --</option>';
                if (!dunId) return;

                const dms = window.pruHierarchy[parId].duns[dunId].dms;

                for (const kod in dms) {
                    dmSelect.innerHTML += `<option value="${kod}">${dms[kod].namadm}</option>`;
                }

                dmSelect.disabled = false;
            }

            // ==============================
            // DATATABLE
            // ==============================
            table = $('#pengundiTable').DataTable({

                processing: true,
                serverSide: false,
                stateSave: true,
                searching: true,
                paging: true,
                fixedHeader: true,
                orderCellsTop: true,

                columns: [
                    { data: 'kod_lokaliti', visible: false },
                    { data: 'nama_lokaliti' },
                    { data: 'saluran_1', render: renderSaluranLink },
                    { data: 'saluran_2', render: renderSaluranLink },
                    { data: 'saluran_3', render: renderSaluranLink },
                    { data: 'saluran_4', render: renderSaluranLink },
                    { data: 'saluran_5', render: renderSaluranLink },
                    { data: 'saluran_6', render: renderSaluranLink },
                    { data: 'saluran_7', render: renderSaluranLink },
                    { data: 'total' }
                ],

                ajax: {
                    url: "{{ route('pengundi.list_data') }}",
                    type: "POST",
                    data: function (d) {
                        d.parlimen = parSelect.value;
                        d.dun = dunSelect.value;
                        d.dm = dmSelect.value;
                        d.type = typeSelect.value;
                        d.series = seriesSelect.value;
                    },
                    dataSrc: json => json.data
                },

                // 🔥 SAVE FILTERS INSIDE DATATABLE STATE
                stateSaveParams: function (settings, data) {
                    data.filters = {
                        type: typeSelect.value,
                        series: seriesSelect.value,
                        par: parSelect.value,
                        dun: dunSelect.value,
                        dm: dmSelect.value
                    };
                },

                // 🔥 RESTORE FILTERS CLEANLY
                stateLoadParams: async function (settings, data) {

                    if (!data.filters) return;

                    typeSelect.value = data.filters.type;
                    seriesSelect.value = data.filters.series;

                    await loadHierarchy();

                    parSelect.value = data.filters.par;
                    buildDun(parSelect.value);

                    dunSelect.value = data.filters.dun;
                    buildDm(parSelect.value, dunSelect.value);

                    dmSelect.value = data.filters.dm;
                },

                language: {
                    emptyTable: "Sila pilih filter untuk lihat data"
                }
            });

            // ==============================
            // EVENTS
            // ==============================
            typeSelect.addEventListener('change', async function () {
                await loadHierarchy();
                table.ajax.reload();
            });

            seriesSelect.addEventListener('change', async function () {
                await loadHierarchy();
                table.ajax.reload();
            });

            parSelect.addEventListener('change', function () {
                buildDun(this.value);
                table.ajax.reload();
            });

            dunSelect.addEventListener('change', function () {
                buildDm(parSelect.value, this.value);
                table.ajax.reload();
            });

            dmSelect.addEventListener('change', function () {
                table.ajax.reload();
            });

            function renderSaluranLink(data, type, row, meta) {
                if (data > 0) {
                    const columnName = meta.settings.aoColumns[meta.col].data;
                    return `<a href="${row['link_' + columnName]}">${data}</a>`;
                }
                return data;
            }

        });
    </script>

@endpush