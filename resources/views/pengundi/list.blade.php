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

            <div class="row mb-4">
                <div class="col-md-3">
                    <label class="form-label">Parlimen</label>
                    <select id="parlimenSelect" class="form-select">
                        <option value="">-- Pilih Parlimen --</option>
                        @foreach($parlimens as $par)
                            <option value="{{ $par->id }}">{{ $par->namapar }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">DUN</label>
                    <select id="dunSelect" class="form-select" disabled>
                        <option value="">-- Pilih DUN --</option>
                        @foreach($duns as $dun)
                            <option value="{{ $dun->kod_dun }}" data-parent="{{ $dun->parlimen_id }}">
                                {{ $dun->namadun }} ({{ $dun->kod_dun }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">DM</label>
                    <select id="dmSelect" class="form-select" disabled>
                        <option value="">-- Pilih DM --</option>
                        @foreach($dms as $dm)
                            <option value="{{ $dm->koddm }}" data-parent="{{ $dm->kod_dun }}">{{ $dm->koddm }}</option>
                        @endforeach
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

            <div class="mb-3">
                <label for="pilihanRayaType" class="form-label">Jenis Pilihan Raya</label>
                <select name="pilihan_raya_type" id="pilihanRayaType" class="form-select">
                    <option value="">-- Pilih Jenis --</option>
                    @foreach($pilihanRayaTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="pilihanRayaSeries" class="form-label">Series Pilihan Raya</label>
                <select name="pilihan_raya_series" id="pilihanRayaSeries" class="form-select">
                    <option value="">-- Pilih Series --</option>
                    @foreach($pilihanRayaSeries as $series)
                        <option value="{{ $series }}">{{ $series }}</option>
                    @endforeach
                </select>
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

            function setupCascade(parent, child) {
                parent.addEventListener('change', function () {
                    const value = this.value;

                    child.value = "";
                    child.disabled = true;

                    Array.from(child.options).forEach(opt => {
                        if (!opt.value) return;
                        opt.hidden = opt.dataset.parent != value;
                    });

                    if (value) child.disabled = false;
                    child.dispatchEvent(new Event('change'));
                });
            }

            const par = document.getElementById('parlimenSelect');
            const dun = document.getElementById('dunSelect');
            const dm = document.getElementById('dmSelect');

            setupCascade(par, dun);
            setupCascade(dun, dm);

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
                const allSelected = par.value && dun.value && dm.value && $('#pilihanRayaSeries').val();

                if (!allSelected) {
                    if ($.fn.dataTable.isDataTable('#pengundiTable')) {
                        table.clear().draw();  // Clear table if not all selected
                    }
                    return;
                }

                // If table is not initialized yet, initialize it
                if (!$.fn.dataTable.isDataTable('#pengundiTable')) {
                    table = $('#pengundiTable').DataTable({
                        processing: true,
                        serverSide: false,
                        searching: true,
                        paging: true,
                        info: true,
                        columns: [
                            {
                                data: 'kod_lokaliti',
                                title: 'Kod Lokaliti',
                                visible: false // This will hide the `kod_lokaliti` column
                            },

                            { data: 'nama_lokaliti', title: 'Lokaliti' },
                            {
                                data: 'saluran_1',
                                title: 'Saluran 1',
                                render: renderSaluranLink
                            },
                            {
                                data: 'saluran_2',
                                title: 'Saluran 2',
                                render: renderSaluranLink
                            },
                            {
                                data: 'saluran_3',
                                title: 'Saluran 3',
                                render: renderSaluranLink
                            },
                            {
                                data: 'saluran_4',
                                title: 'Saluran 4',
                                render: renderSaluranLink
                            },
                            {
                                data: 'saluran_5',
                                title: 'Saluran 5',
                                render: renderSaluranLink
                            },
                            {
                                data: 'saluran_6',
                                title: 'Saluran 6',
                                render: renderSaluranLink
                            },
                            {
                                data: 'saluran_7',
                                title: 'Saluran 7',
                                render: renderSaluranLink
                            },
                            { data: 'total', title: 'Total' },
                            {
                                data: 'kod_lokaliti',
                                title: 'Kod Lokaliti',
                                visible: false  // Hide the kod_lokaliti column
                            }
                        ],
                        ajax: {
                            url: "{{ route('pengundi.list_data') }}",
                            type: "POST",
                            data: function (d) {
                                console.log(d);
                                d.parlimen = par.value || null;
                                d.dun = dun.value || null;
                                d.dm = dm.value || null;
                                d.pilihan_raya_type = $('#pilihanRayaType').val() || null;
                                d.pilihan_raya_series = $('#pilihanRayaSeries').val() || null;
                                d._token = $('meta[name="csrf-token"]').attr('content');
                            },
                            dataSrc: function (json) {
                                console.log(json);
                                return json.data;
                            }
                        },
                        language: {
                            emptyTable: "Sila pilih filter untuk lihat data"
                        },
                        order: [[0, 'asc']]  // Orders by the first column (`kod_lokaliti`)
                    });
                } else {
                    // If table is already initialized, just reload the data
                    table.ajax.reload();
                }
            }

            function renderSaluranLink(data, type, row, meta) {
                if (data > 0) {
                    const columnName = meta.settings.aoColumns[meta.col].data; // e.g., 'saluran_1'
                    return `<a href="${row['link_' + columnName]}" target="_blank">${data}</a>`;
                }
                return data;
            }



            // Trigger reloadTableIfReady whenever there's a change in the filters
            $('#parlimenSelect, #dunSelect, #dmSelect, #pilihanRayaType, #pilihanRayaSeries').on('change', reloadTableIfReady);


            $('#parlimenSelect, #dunSelect, #dmSelect, #pilihanRayaType, #pilihanRayaSeries')
                .on('change', reloadTableIfReady);
        });

    </script>

@endpush