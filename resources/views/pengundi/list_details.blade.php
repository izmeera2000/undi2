@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/vendors/datatables/datatables.css') }}">
@endpush
@section('breadcrumb')
    @php
        $crumbs 


    @endphp
@endsection

@section('content')



    <div class="card">
        <div class="card-body">

            <table id="pengundiTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Kad Pengenalan</th>
                        <th>Jantina</th>
                        <th>Bangsa</th>
                        <th>Umur</th>
                        <th>No Siri</th>
                        <th>PM</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

        </div>
    </div>
@endsection

@push('scripts')
    <!-- Make sure jQuery is loaded BEFORE DataTables -->
    <script src="{{ asset('assets/vendors/datatables/datatables.js') }}"></script>

    <script>
        $(document).ready(function () {
            $('#pengundiTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('pengundi.list_details_data') }}',
                    type: 'POST',
                    data: {
                        parlimen: '{{ $parlimen ?? "" }}',
                        dun: '{{ $dun_kod ?? "" }}',
                        dm: '{{ $dm ?? "" }}',
                        lokaliti: '{{ $lokaliti ?? "" }}',
                        saluran: '{{ $saluran ?? "" }}',
                        pilihan_raya_type: '{{ $pilihan_raya_type ?? "" }}',
                        pilihan_raya_series: '{{ $pilihan_raya_series ?? "" }}',
                        _token: $('meta[name="csrf-token"]').attr('content'),
                    }
                },
                columns: [
                    { data: 'id' },
                    { data: 'nama' },
                    { data: 'nokp_baru' },
                    { data: 'jantina' },
                    { data: 'bangsa' },
                    { data: 'umur' },
                    { data: 'no_siri' },
                    { data: 'alamat_spr' }
                ],
                pageLength: 25,
                order: [[0, 'asc']],
                responsive: true
            });
        });
    </script>
@endpush