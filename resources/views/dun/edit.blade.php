@extends('layouts.app')

@section('title', 'Edit Dun')

@section('breadcrumb')
    @php
        $crumbs = [
            ['label' => 'Dun', 'url' => route('dun.index')],
            ['label' => 'Edit', 'url' => route('dun.edit', $dun->id)],
        ];
    @endphp
@endsection

@section('content')
<section class="section">
    <div class="card g-4 mb-4">
        <div class="card-header">
            <h5>Edit Dun</h5>
        </div>
        <div class="card-body">
            <form id="editDunForm">

                <div class="mb-3">
                    <label class="form-label">Kod Dun</label>
                    <input type="text" name="kod_dun" class="form-control"
                        value="{{ $dun->kod_dun }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Dun</label>
                    <input type="text" name="namadun" class="form-control"
                        value="{{ $dun->namadun }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Parlimen</label>
                    <select name="parlimen_id" class="form-control" required>
                        @foreach($parlimens as $parlimen)
                            <option value="{{ $parlimen->id }}"
                                {{ $dun->parlimen_id == $parlimen->id ? 'selected' : '' }}>
                                {{ $parlimen->namapar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="_method" value="PUT">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('dun.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
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

    $('#editDunForm').submit(function (e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.ajax({
            url: "{{ route('dun.update', $dun->id) }}",
            method: 'POST', // POST + _method=PUT
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: formData,
            success: function () {
                alert('Dun updated successfully!');
                window.location.href = "{{ route('dun.index') }}";
            },
            error: function (xhr) {
                alert('Error updating dun!');
                console.error(xhr.responseText);
            }
        });
    });
});
</script>
@endpush
