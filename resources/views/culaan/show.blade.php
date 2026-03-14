@extends('layouts.app')

@section('title', 'Culaan Details')

@section('breadcrumb')
    @php
        $crumbs = [
            ['label' => 'Culaan', 'url' => route('culaan.index')],
            ['label' => $culaan->name]
        ];
    @endphp
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables/datatables.css') }}">
@endpush


@section('content')

    <section class="section">

        <!-- Culaan Info -->
        <div class="card mb-3">
            <div class="card-header">
                <div class="row g-3 align-items-center w-100">

                    <div class="d-flex flex-wrap justify-content-md-end gap-2">

                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#activityModal">
                            <i class="bi bi-clock-history"></i> History Culaan
                        </button>
                        <a href="{{ route('culaan.analytics', $culaan) }}" class="btn btn-primary ">
                            <i class="fas fa-chart-line me-1"></i> Analytics
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">

                <h4>{{ $culaan->name }}</h4>

                <p>
                    Election :
                    {{ $culaan->election?->type }}
                    {{ $culaan->election?->number }}
                    ({{ $culaan->election?->year }})
                </p>

                <p>Date : {{ $culaan->date }}</p>

            </div>
        </div>


        <!-- Pengundi Table -->
        <div class="card g-4 mb-4">


            <div class="card-header">
                <div class="row g-3 align-items-center w-100">
                    <!-- Title -->
                    <div class="col-md-4 col-12">
                        <h5 class="card-title mb-0">Culaan Pengundi</h5>
                    </div>

                    <!-- Buttons -->
                    <div class="col-md-8  col-12">
                        <div class="d-flex flex-wrap justify-content-md-end gap-2">

                            <button class="btn btn-primary " data-bs-toggle="modal" data-bs-target="#addPengundiModal">
                                <i class="bi bi-plus me-1"></i> Add Pengundi
                            </button>

                            <a href="{{ route('culaan.pengundi.bulkimport', $culaan) }}" class="btn btn-success ">
                                <i class="bi bi-upload me-1"></i> Bulk Import
                            </a>

                            <div class="btn-group" id="pdfButtonGroup" style=" ">

                                <!-- Main Action -->
                                <button id="generatePdf" type="button" class="btn btn-primary">
                                    Generate PDF
                                </button>

                                <!-- Split Dropdown Toggle -->
                                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>

                                <!-- Dropdown Menu -->
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" id="forceUpdatePdf">
                                            Force Generate
                                        </a>
                                    </li>



                                </ul>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-2">

                <div class="row mb-3 p-2">

                    <div class="col-md-3">
                        <label>Nama / No KP</label>
                        <input type="text" id="filter_search" class="form-control" placeholder="Search name or IC">
                    </div>

                    <div class="col-md-3">
                        <label for="filter_lokaliti">Lokaliti</label>
                        <select id="filter_lokaliti" class="form-control">
                            <option value="">All Lokaliti</option>
                            @foreach($lokalitiList as $lokaliti)
                                <option value="{{ $lokaliti->kod_lokaliti }}">{{ $lokaliti->nama_lokaliti }}
                                    ({{  $lokaliti->kod_lokaliti}})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Status Culaan</label>


                        <select id="filter_status" class="form-select">
                            <option value="">All</option>
                            <option value="D">BN</option>
                            <option value="C">PAS</option>
                            <option value="A">PH</option>
                            <option value="E">TP</option>
                            <option value="O">BC</option>
                        </select>


                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button id="applyFilter" class="btn btn-primary w-100">
                            Filter
                        </button>
                    </div>

                </div>
                <div class="table-responsive">

                    <table id="pengundiTable" class="table table-hover">

                        <thead>
                            <tr>
                                <th></th>
                                <th>ID</th>
                                <th>Pengundi</th>
                                <th>Lokaliti</th>
                                <th>Kategori/ Status Pengundi</th>
                                <th>Status Culaan</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>

                    </table>
                </div>

            </div>

        </div>

    </section>

    <!-- ADD PENGUNDI MODAL -->
    <div class="modal fade" id="addPengundiModal">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Add Pengundi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Form for adding pengundi -->
                <form id="addPengundiForm" method="POST">
                    <div class="modal-body">
                        <div class="row g-3">

                            <!-- Lokaliti Info -->
                            <div class="col-md-6">
                                <label for="lokaliti">Lokaliti</label>
                                <select name="lokaliti" class="form-control" id="lokaliti">
                                    <option value="">Select Lokaliti</option>
                                    @foreach($lokalitiList as $lokaliti)
                                        <option value="{{ $lokaliti->nama_lokaliti }},{{ $lokaliti->kod_lokaliti }}">
                                            {{ $lokaliti->nama_lokaliti }} ({{ $lokaliti->kod_lokaliti }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label>PM</label>
                                <input type="text" name="pm" class="form-control" placeholder="PM">
                            </div>

                            <div class="col-md-6">
                                <label>No Siri</label>
                                <input type="number" name="no_siri" class="form-control" placeholder="No Siri">
                            </div>

                            <div class="col-md-6">
                                <label>Saluran</label>
                                <input type="number" name="saluran" class="form-control" placeholder="Saluran">
                            </div>

                            <!-- Personal Info -->
                            <div class="col-md-8">
                                <label>Nama</label>
                                <input type="text" name="nama" class="form-control" placeholder="Nama penuh" required>
                            </div>

                            <div class="col-md-4">
                                <label>No KP</label>
                                <input type="number" name="no_kp" class="form-control" placeholder="No KP" required>
                            </div>

                            <div class="col-md-4">
                                <label>Jantina</label>
                                <select name="jantina" class="form-select">
                                     <option value="L">Lelaki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Umur</label>
                                <input type="number" name="umur" class="form-control" placeholder="Umur">
                            </div>

                            <div class="col-md-4">
                                <label for="bangsa">Bangsa</label>
                                <select name="bangsa" class="form-control" id="bangsa">
                                    <option value="">Select Bangsa</option>
                                    <option value="M">Melayu</option>
                                    <option value="C">Cina</option>
                                    <option value="I">India</option>
                                    <option value="L">Lain-Lain</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="kategori_pengundi">Kategori Pengundi</label>
                                <select name="kategori_pengundi" class="form-control" id="kategori_pengundi">
                                    <option value="">Select Kategori Pengundi</option>
                                    <option value="Pengundi Luar">Pengundi Luar</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label>Status Pengundi</label>
                                <input type="text" name="status_pengundi" class="form-control"
                                    placeholder="Status Pengundi">
                            </div>

                            <div class="col-md-4">
                                <label>Status Culaan</label>
                                <select name="status_culaan" class="form-select">
                                    <option value="O">Belum Cula</option>
                                    <option value="D">BN</option>
                                    <option value="A">PH</option>
                                    <option value="C">PAS</option>
                                    <option value="E">Tidak Pasti</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Cawangan</label>
                                <input type="text" name="cawangan" class="form-control" placeholder="Cawangan">
                            </div>

                            <div class="col-md-3">
                                <label>No Ahli</label>
                                <input type="number" name="no_ahli" class="form-control" placeholder="No Ahli">
                            </div>

                            <div class="col-md-6">
                                <label>Alamat</label>
                                <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat penuh"></textarea>
                            </div>

                            <!-- Status Info -->
                            <div class="col-md-6">
                                <label for="status_ahli">Status Ahli</label>
                                <select name="status_ahli" class="form-control" id="status_ahli">
                                    <option value="">Select Status</option>
                                    <option value="AHLI">Ahli</option>
                                    <!-- Add more statuses as needed -->
                                </select>
                            </div>

                            <!-- Kategori Info -->
                            <div class="col-md-6">
                                <label for="kategori_ahli">Kategori Ahli</label>
                                <select name="kategori_ahli" class="form-control" id="kategori_ahli">
                                    <option value="">Select Kategori</option>
                                    @foreach($groupsList as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="savePengundiBtn">Save</button>
                    </div>

                </form>

            </div>
        </div>
    </div>



    <div class="modal fade" id="activityModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Culaan Activity Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

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
@endsection



@push('scripts')

    <script src="{{ asset('assets/vendors/datatables/datatables.js') }}"></script>

    <script>

        let table;

        function format(row) {

            return `
            <div class="p-3">

                <div class="row">

                    <div class="col-md-3">
                        <strong>No Siri</strong><br>
                        ${row.no_siri ?? '-'}
                    </div>

                    <div class="col-md-3">
                        <strong>Saluran</strong><br>
                        ${row.saluran ?? '-'}
                    </div>

                    <div class="col-md-3">
                        <strong>Bangsa</strong><br>
                        ${row.bangsa ?? '-'}
                    </div>

                    <div class="col-md-3">
                        <strong>Umur</strong><br>
                        ${row.umur ?? '-'}
                    </div>


                    <div class="col-md-3 mt-3">
                        <strong>Cawangan</strong><br>
                        ${row.nama_cwgn ?? '-'}
                    </div>

                </div>

            </div>
        `;
        }

        $(function () {

            table = $('#pengundiTable').DataTable({

                processing: true,
                serverSide: true,
                searching: false,

                ajax: {
                    url: "{{ route('culaan.pengundi.data', $culaan) }}",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: function (d) {

                        d.lokaliti = $('#filter_lokaliti').val();
                        d.status_culaan = $('#filter_status').val();
                        d.search_name = $('#filter_search').val();

                    }
                },

                columns: [

                    {
                        orderable: false,
                        data: null,
                        defaultContent: '<span class="toggle-child"><i class="bi bi-plus-circle-fill text-primary"></i></span>'
                    },

                    { data: 'id' },

                    {
                        data: 'pengundi_details',

                    },


                    {
                        data: 'lokaliti_details',

                    },
                    {
                        data: 'pengundi_details2',

                    },

                    { data: 'status_culaan', orderable: false },

                    { data: 'actions', orderable: false }

                ]

            });


            // CHILD ROW TOGGLE
            $('#pengundiTable tbody').on('click', '.toggle-child', function () {

                let tr = $(this).closest('tr');
                let row = table.row(tr);
                let icon = $(this).find('i');

                if (row.child.isShown()) {

                    row.child.hide();
                    tr.removeClass('shown');

                    icon.removeClass('bi-dash-circle text-danger')
                        .addClass('bi-plus-circle-fill text-primary');

                } else {

                    row.child(format(row.data())).show();
                    tr.addClass('shown');

                    icon.removeClass('bi-plus-circle-fill text-primary')
                        .addClass('bi-dash-circle text-danger');

                }

            });

        });





        /* CHANGE STATUS */

        $(document).on('click', '.change-status', function () {

            let id = $(this).data('id');
            let status = $(this).data('status');

            $.post("{{ route('culaan.pengundi.updateStatus', $culaan) }}", {

                id: id,
                status: status,
                _token: $('meta[name="csrf-token"]').attr('content')

            }, function () {

                table.ajax.reload(null, false);

            });

        });



        /* DELETE */

        $(document).on('click', '.delete-pengundi', function () {

            if (!confirm("Remove pengundi?")) return;

            let id = $(this).data('id');

            $.post("{{ route('culaan.pengundi.deletePengundi', $culaan) }}", {

                id: id,
                _token: $('meta[name="csrf-token"]').attr('content')

            }, function () {

                table.ajax.reload(null, false);

            });

        });



        /* ADD PENGUNDI */

        $('#savePengundiBtn').click(function () {
            // Get form data
            var formData = $('#addPengundiForm').serialize(); // Serializes the form data

            // Make the AJAX POST request
            $.post("{{ route('culaan.pengundi.store', $culaan) }}", formData, function (response) {
                // Handle success response
                if (response.success) {
                    alert('Pengundi added successfully!');
                    $('#addPengundiModal').modal('hide'); // Close the modal
                    $('#addPengundiForm')[0].reset(); // Reset the form
            table.ajax.reload();

                } else {
                    alert('There was an error adding the pengundi.');
                }
            })
                .fail(function (xhr, status, error) {
                    // Handle error response
                    alert('Something went wrong. Please try again.');
                });
        });


        $('#applyFilter').click(function () {

            table.ajax.reload();

        });
        async function exportPdf(force = false) {

            const filters = {
                lokaliti: document.getElementById('filter_lokaliti').value,
                status_culaan: document.getElementById('filter_status').value,
                search_name: document.getElementById('filter_search').value,
                force: force
            };

            const response = await fetch("{{ route('culaan.exportpdf', $culaan) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(filters)
            });

            const data = await response.json();

            if (data.exists) {

                window.open(data.url, "_blank");
                toastr.success("Opening existing PDF");

            } else {

                toastr.info("Generating PDF in background");

            }
        }

        document.getElementById('generatePdf').addEventListener('click', function (e) {
            e.preventDefault();
            exportPdf(false); // FORCE regenerate
        });

        document.getElementById('forceUpdatePdf').addEventListener('click', function (e) {
            e.preventDefault();
            exportPdf(true); // Use existing if available
        });

    </script>

    <script>

        let activityTable;

        $('#activityModal').on('shown.bs.modal', function () {

            if (!activityTable) {

                activityTable = $('#activityTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('culaan.activity', $culaan->id) }}",

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

    </script>

@endpush