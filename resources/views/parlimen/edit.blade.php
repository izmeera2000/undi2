@extends('layouts.app')

@section('title', 'Edit Parlimen')

@section('breadcrumb')
    @php
        $crumbs = [
            ['label' => 'Parlimen', 'url' => route('parlimen.index')],
            ['label' => 'Edit', 'url' => route('parlimen.edit', $parlimen->id)],
        ];
    @endphp
@endsection

@section('content')
    <section class="section">
        <div class="card g-4 mb-4">
            <div class="card-header">
                <h5>Edit Parlimen</h5>
            </div>
            <div class="card-body">
                <form id="editParlimenForm">
                    <div class="mb-3">
                        <label class="form-label">Kod Parlimen</label>
                        <input type="text" name="kod_par" class="form-control" value="{{ $parlimen->kod_par }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Parlimen</label>
                        <input type="text" name="namapar" class="form-control" value="{{ $parlimen->namapar }}" required>
                    </div>

                    <input type="hidden" name="_method" value="PUT">


                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('parlimen.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="saveEditParlimenBtn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

           $('#editParlimenForm').submit(function (e) {
    e.preventDefault();
    const formData = $(this).serialize(); // includes _method=PUT

    $.ajax({
        url: "{{ route('parlimen.update', $parlimen->id) }}",
        method: 'POST',  // <- POST instead of PUT
        headers: {
            'X-CSRF-TOKEN': csrfToken
        },
        data: formData,  // serialized form includes _method=PUT
        success: function () {
            alert('Parlimen updated successfully!');
            window.location.href = "{{ route('parlimen.index') }}";
        },
        error: function (xhr) {
            alert('Error updating parlimen!');
            console.error(xhr.responseText);
        }
    });
});


        });
    </script>
@endpush