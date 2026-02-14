@extends('layouts.app')

@section('title', 'Parlimen List')



@section('breadcrumb')
    @php
        // Build dynamic crumbs based on request
        $crumbs = [
            ['label' => 'Parliment', 'url' => route('parlimen.index')],
            ['label' => 'List', 'url' => route('parlimen.index')],
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
                            <input type="text" id="parlimenSearch" class="form-control border-start-0 ps-0"
                                placeholder="Search Parlimen...">
                        </div>
                    </div>
                    <div class="col-md-8 col-12">
                        <div class="d-flex flex-wrap justify-content-md-end gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addParlimenModal">
                                <i class="bi bi-plus-lg me-1"></i> Add Parlimen
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-1">
                <div class="table-responsive">
                    <table id="parlimenTable" class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>

                                <th>Kod Parlimen</th>
                                <th>Nama Parlimen</th>
                                <th>Actions</th>
                            </tr>
                        </thead>


                        <tbody></tbody>
                    </table>
                </div>
            </div>



        </div>
    </section>

    {{-- Add Parlimen Modal --}}
    <div class="modal fade" id="addParlimenModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="addParlimenForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Parlimen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kod Parlimen</label>
                            <input type="text" name="kod_parlimen" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Parlimen</label>
                            <input type="text" name="nama_parlimen" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Parlimen</button>
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

            const table = $('#parlimenTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('parlimen.data') }}",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },

                    error: function (xhr) {
                        if (xhr.status === 401) {
                            window.location.href = "{{ route('login') }}";
                        }
                    }
                },
                columns: [
                    { data: 'kod_par', name: 'kod_par' },
                    { data: 'name', name: 'namapar' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ]
            });

            // Search input
            $('#parlimenSearch').on('keyup', function () {
                table.search(this.value).draw();
            });

            // Add Parlimen
            $('#addParlimenForm').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('parlimen.store') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function () {
                        $('#addParlimenModal').modal('hide');
                        table.ajax.reload();
                    },
                    error: function (err) {
                        alert('Error saving parlimen!');
                    }
                });
            });

        });
    </script>
@endpush