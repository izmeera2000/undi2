@extends('layouts.app')

@section('title', 'DUN List')



@section('breadcrumb')
    @php
        // Build dynamic crumbs based on request
        $crumbs = [
            ['label' => 'DUN', 'url' => route('dun.index')],
            ['label' => 'List', 'url' => route('dun.index')],
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
                            <input type="text" id="dunSearch" class="form-control border-start-0 ps-0"
                                placeholder="Search DUN...">
                        </div>
                    </div>
                    <div class="col-md-8 col-12">
                        <div class="d-flex flex-wrap justify-content-md-end gap-2">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDUNModal">
                                <i class="bi bi-plus-lg me-1"></i> Add DUN
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body p-1">
                <div class="table-responsive">
                    <table id="dunTable" class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>

                                <th>Kod DUN</th>
                                <th>Nama DUN</th>
                                <th>Actions</th>
                            </tr>
                        </thead>


                        <tbody></tbody>
                    </table>
                </div>
            </div>



        </div>
    </section>

    {{-- Add DUN Modal --}}
    <div class="modal fade" id="addDUNModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="addDUNForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Add DUN</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Parlimen</label>
                            <input type="text" name="kod_dun" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama DUN</label>
                            <input type="text" name="nama_dun" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save DUN</button>
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

            const table = $('#dunTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('dun.data') }}",
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
                    { data: 'parlimen_name', name: 'parlimen.namapar' },
                    { data: 'dun_name', name: 'namadun' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false },
                ]

            });

            // Search input
            $('#dunSearch').on('keyup', function () {
                table.search(this.value).draw();
            });

            // Add DUN
            $('#addDUNForm').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('dun.store') }}",
                    method: 'POST',
                    
                    data: $(this).serialize(),
                    success: function () {
                        $('#addDUNModal').modal('hide');
                        table.ajax.reload();
                    },
                    error: function (err) {
                        alert('Error saving dun!');
                    }
                });
            });


            $('#dunTable').on('click', '.delete-dun', function () {
                const dunId = $(this).data('id');

                if (confirm('Are you sure you want to delete this DM?')) {
                    $.ajax({
                        url: "{{ route('dun.destroy', ':id') }}".replace(':id', dunId), // dynamically insert ID
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },

                        data: {
                            _method: 'DELETE' // Laravel expects DELETE
                        },
                        success: function () {
                            table.ajax.reload();
                            alert('DM deleted successfully.');
                        },
                        error: function (xhr) {
                            alert('Error deleting DM!');
                            console.error(xhr.responseText);
                        }
                    });
                }
            });


        });
    </script>
@endpush