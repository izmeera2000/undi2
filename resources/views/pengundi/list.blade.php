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
                        <th>Lokaliti</th>

                        <th>Saluran 1</th>
                        <th>Saluran 2</th>
                        <th>Saluran 3</th>
                        <th>Saluran 4</th>
                        <th>Saluran 5</th>
                        <th>Saluran 6</th>
                        <th>Saluran 7</th>
                        {{-- <th>Saluran 8</th> --}}
                        <th>Total</th>

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

            // function setupCascade(parent, child) {
            //     parent.addEventListener('change', function () {
            //         const value = this.value;

            //         child.value = "";
            //         child.disabled = true;

            //         Array.from(child.options).forEach(opt => {
            //             if (!opt.value) return;
            //             opt.hidden = opt.dataset.parent != value;
            //         });

            //         if (value) child.disabled = false;
            //         child.dispatchEvent(new Event('change'));
            //     });
            // }

            // const par = document.getElementById('parlimenSelect');
            // const dun = document.getElementById('dunSelect');
            // const dm = document.getElementById('dmSelect');

            // setupCascade(par, dun);
            // setupCascade(dun, dm);

            // ===============================
            // DataTable Init
            // ===============================
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let table;  // Declare table variable outside so it's accessible globally

            // Function to initialize or reload the table
            function reloadTableIfReady() {
                const par = document.getElementById('parlimenSelect');
                const dun = document.getElementById('dunSelect');
                const dm = document.getElementById('dmSelect');
                const series = document.getElementById('pilihanRayaSeries');
                const type = document.getElementById('pilihanRayaType');

                // Only proceed if all required filters are selected
                const allSelected = par.value && dun.value && dm.value && type.value && series.value;

                // Clear table if filters are not ready
                if (!allSelected) {
                    if ($.fn.dataTable.isDataTable('#pengundiTable')) {
                        table.clear().draw();  // Clear table
                    }
                    return;
                }

                // Initialize DataTable if not yet
                if (!$.fn.dataTable.isDataTable('#pengundiTable')) {
                    table = $('#pengundiTable').DataTable({
                        processing: true,
                        serverSide: false,
                        searching: true,
                        paging: true,
                        info: true,
                        columns: [
                            { data: 'kod_lokaliti', title: 'Kod Lokaliti', visible: false },
                            { data: 'nama_lokaliti', title: 'Lokaliti' },
                            { data: 'saluran_1', title: 'Saluran 1', render: renderSaluranLink },
                            { data: 'saluran_2', title: 'Saluran 2', render: renderSaluranLink },
                            { data: 'saluran_3', title: 'Saluran 3', render: renderSaluranLink },
                            { data: 'saluran_4', title: 'Saluran 4', render: renderSaluranLink },
                            { data: 'saluran_5', title: 'Saluran 5', render: renderSaluranLink },
                            { data: 'saluran_6', title: 'Saluran 6', render: renderSaluranLink },
                            { data: 'saluran_7', title: 'Saluran 7', render: renderSaluranLink },
                            { data: 'total', title: 'Total' },
                            { data: 'kod_lokaliti', title: 'Kod Lokaliti', visible: false }
                        ],
                        ajax: {
                            url: "{{ route('pengundi.list_data') }}",
                            type: "POST",
                            data: function (d) {
                                d.parlimen = par.value;
                                d.dun = dun.value;
                                d.dm = dm.value;
                                d.type = type.value;
                                d.series = series.value;
                                d._token = $('meta[name="csrf-token"]').attr('content');
                            },
                            dataSrc: function (json) {
                                return json.data;  // Return only data array
                            }
                        },
                        language: {
                            emptyTable: "Sila pilih filter untuk lihat data"
                        },
                        order: [[0, 'asc']] // Sort by kod_lokaliti
                    });
                } else {
                    // Table already exists → just reload
                    table.ajax.reload();
                }
            }

            parlimenSelect.addEventListener('change', reloadTableIfReady);
            dunSelect.addEventListener('change', reloadTableIfReady);
            dmSelect.addEventListener('change', reloadTableIfReady);
            pilihanRayaType.addEventListener('change', reloadTableIfReady);
            pilihanRayaSeries.addEventListener('change', reloadTableIfReady);



            function renderSaluranLink(data, type, row, meta) {
                if (data > 0) {
                    const columnName = meta.settings.aoColumns[meta.col].data; // e.g., 'saluran_1'
                    return `<a href="${row['link_' + columnName]}" target="_blank">${data}</a>`;
                }
                return data;
            }



            // // Trigger reloadTableIfReady whenever there's a change in the filters
            // $('#parlimenSelect, #dunSelect, #dmSelect, #pilihanRayaType, #pilihanRayaSeries').on('change', reloadTableIfReady);


            // $('#parlimenSelect, #dunSelect, #dmSelect, #pilihanRayaType, #pilihanRayaSeries')
            //     .on('change', reloadTableIfReady);
        });
        const typeSelect = document.getElementById('pilihanRayaType');
        const seriesSelect = document.getElementById('pilihanRayaSeries');
        typeSelect.addEventListener('change', loadHierarchyDropdowns);
        seriesSelect.addEventListener('change', loadHierarchyDropdowns);

        function loadHierarchyDropdowns() {

            // Require both type and series to be selected
            if (!typeSelect.value || !seriesSelect.value) return;
            console.log('ok');
            fetch(`{{ route('pengundi.ajax.pru.hierarchy') }}?type=${typeSelect.value}&series=${seriesSelect.value}`)
                .then(res => res.json())
                .then(data => {

                    const hierarchy = {};

                    data.forEach(row => {

                        // Parlimen level
                        if (!hierarchy[row.parlimen_id]) {
                            hierarchy[row.parlimen_id] = { namapar: row.namapar, duns: {} };
                        }

                        // DUN level
                        if (!hierarchy[row.parlimen_id].duns[row.kod_dun]) {
                            hierarchy[row.parlimen_id].duns[row.kod_dun] = { namadun: row.namadun, dms: {} };
                        }

                        // DM level
                        if (!hierarchy[row.parlimen_id].duns[row.kod_dun].dms[row.koddm]) {
                            hierarchy[row.parlimen_id].duns[row.kod_dun].dms[row.koddm] = { namadm: row.namadm, lokalitis: [] };
                        }

                        // Lokaliti level
                        hierarchy[row.parlimen_id].duns[row.kod_dun].dms[row.koddm].lokalitis.push({
                            kod_lokaliti: row.kod_lokaliti,
                            nama_lokaliti: row.nama_lokaliti
                        });
                    });

                    console.log(hierarchy);

                    // Save globally for other dropdowns
                    window.pruHierarchy = hierarchy;

                    // Build the first dropdown
                    buildParlimenDropdown(hierarchy);

                    // Reset lower dropdowns
                    resetDropdown(document.getElementById('dunSelect'));
                    resetDropdown(document.getElementById('dmSelect'));
                    resetDropdown(document.getElementById('lokalitiSelect'));
                })
                .catch(err => console.error("Error loading PRU hierarchy:", err));
        }

        // Utility to reset a dropdown
        function resetDropdown(select) {
            if (!select) return;
            select.innerHTML = '<option value="">-- Pilih --</option>';
            select.disabled = true;
        }

        function buildParlimenDropdown(hierarchy) {

            const select = document.getElementById('parlimenSelect');
            select.innerHTML = '<option value="">-- Pilih Parlimen --</option>';

            for (const parId in hierarchy) {
                select.innerHTML += `
                                                        <option value="${parId}">
                                                            ${hierarchy[parId].namapar}
                                                        </option>
                                                    `;
            }

            select.disabled = false;
        }




        document.getElementById('parlimenSelect')
            .addEventListener('change', function () {

                const parId = this.value;
                const dunSelect = document.getElementById('dunSelect');

                dunSelect.innerHTML = '<option value="">-- Pilih DUN --</option>';

                if (!parId) return;

                const duns = window.pruHierarchy[parId].duns;

                for (const kodDun in duns) {
                    dunSelect.innerHTML += `
                                                <option value="${kodDun}">
                                                    ${duns[kodDun].namadun}
                                                </option>
                                            `;
                }

                dunSelect.disabled = false;
            });


        document.getElementById('dunSelect')
            .addEventListener('change', function () {

                const parId = document.getElementById('parlimenSelect').value;
                const kodDun = this.value;
                const dmSelect = document.getElementById('dmSelect');

                dmSelect.innerHTML = '<option value="">-- Pilih DM --</option>';

                if (!kodDun) return;

                const dms = window.pruHierarchy[parId].duns[kodDun].dms;

                for (const koddm in dms) {
                    dmSelect.innerHTML += `
                                            <option value="${koddm}">
                                                ${dms[koddm].namadm}
                                            </option>
                                        `;
                }

                dmSelect.disabled = false;
            });







    </script>

@endpush