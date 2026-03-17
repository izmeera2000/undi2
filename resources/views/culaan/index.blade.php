@extends('layouts.app')

@section('title', 'Culaan')

@section('breadcrumb')
    @php
        // Build dynamic crumbs based on request
        $crumbs = [
            ['label' => 'Pengundi'],
            ['label' => 'Culaan', 'url' => route('culaan.index')],
            ['label' => 'List', 'url' => route('culaan.index')],
        ];
    @endphp
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables/datatables.css') }}">
@endpush

@section('content')

    <section class="section">
        <div class="card g-4 mb-4">

            <div class="card-header">
                <div class="row g-3 align-items-center w-100">

                     
                        <div class="d-flex flex-wrap justify-content-md-end gap-2">

                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCulaanModal">

                                <i class="bi bi-plus-lg me-1"></i> Add Culaan
                            </button>

                        </div>
 
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">

                    <table id="culaanTable" class="table table-bordered table-striped table-hover">

                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Election</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tfoot>
                            <tr>
                                <th></th> <!-- Kod Lokaliti -->
                                <th></th> <!-- Kod Lokaliti -->
                                <th></th> <!-- Lokaliti -->
                                <th></th> <!-- Saluran 1 -->
                                <th></th> <!-- Saluran 2 -->

                            </tr>
                        </tfoot>

                    </table>

                </div>
            </div>

        </div>
    </section>


    <div class="modal fade" id="addCulaanModal">

        <div class="modal-dialog">

            <form id="createCulaanForm" method="POST">

                @csrf

                <div class="modal-content">

                    <div class="modal-header">

                        <h5>Create Culaan</h5>

                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label>Election</label>

                            <select name="election_id" class="form-select">
                                <option value="">-- None Selected --</option>

                                @foreach(App\Models\Election::all() as $e)
                                    <option value="{{$e->id}}">
                                        {{$e->type}} {{$e->number}} ({{$e->year}})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control">
                        </div>

                        <div class="mb-3">

                            <label>Name</label>

                            <textarea name="name" class="form-control"></textarea>

                        </div>

                        <div class="mb-3">

                            <label>Description</label>

                            <textarea name="description" class="form-control"></textarea>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button class="btn btn-primary">

                            Save

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

@endsection



@push('scripts')

    <script src="{{ asset('assets/vendors/datatables/datatables.js') }}"></script>

    <script>
        $(document).ready(function () {

            let table;

            table = $('#culaanTable').DataTable({
                    processing: true,
                    serverSide: false,
                    stateSave: true,
                     paging: true,
                    fixedHeader: true,
                    searching: false,
                
                 ajax: {
                    url: "{{ route('culaan.data') }}",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },

                columns: [
                    { data: 'date', name: 'date' },
                    { data: 'name', name: 'name' },
                    { data: 'election', name: 'election' },
                    { data: 'creator', name: 'creator' },
                    { data: 'actions', orderable: false, searchable: false }
                 ]

            });

        });


        // DELETE Culaan
        $(document).on('click', '.delete-culaan', function () {
            if (!confirm('Are you sure you want to delete this Culaan?')) return;

            let id = $(this).data('id'); // <-- get the ID from the clicked button
            let url = "{{ route('culaan.destroy', ':id') }}";
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function (res) {
                    $('#culaanTable').DataTable().ajax.reload(null, false);
                    alert('Culaan deleted successfully!');
                },
                error: function (xhr) {
                    alert('Error deleting Culaan!');
                }
            });
        });
    </script>

@endpush