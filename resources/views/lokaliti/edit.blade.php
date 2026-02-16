@extends('layouts.app')

@section('title', 'Edit Lokaliti')

@section('breadcrumb')
    @php
        $crumbs = [
            ['label' => 'Lokaliti', 'url' => route('lokaliti.index')],
            ['label' => 'Edit', 'url' => route('lokaliti.edit', $lokaliti->id)],
        ];
    @endphp
@endsection

@section('content')
<section class="section">
    <div class="card g-4 mb-4">
        <div class="card-header">
            <h5>Edit Lokaliti</h5>
        </div>
        <div class="card-body">
            <form id="editLokalitiForm">
                <div class="mb-3">
                    <label class="form-label">Kod Lokaliti</label>
                    <input type="text" name="kod_lokaliti" class="form-control"
                        value="{{ $lokaliti->kod_lokaliti }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Lokaliti</label>
                    <input type="text" name="nama_lokaliti" class="form-control"
                        value="{{ $lokaliti->nama_lokaliti }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">DM</label>
                    <select name="dm_id" class="form-control" required>
                        @foreach($dms as $dm)
                            <option value="{{ $dm->id }}"
                                {{ $lokaliti->dm_id == $dm->id ? 'selected' : '' }}>
                                {{ $dm->namadm }} ({{ $dm->koddm }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Effective From</label>
                    <input type="date" name="effective_from" class="form-control"
                        value="{{ $lokaliti->effective_from ? $lokaliti->effective_from->format('Y-m-d') : '' }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Effective To</label>
                    <input type="date" name="effective_to" class="form-control"
                        value="{{ $lokaliti->effective_to ? $lokaliti->effective_to->format('Y-m-d') : '' }}">
                </div>

                <input type="hidden" name="_method" value="PUT">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('lokaliti.index') }}" class="btn btn-light">Cancel</a>
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

    $('#editLokalitiForm').submit(function (e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.ajax({
            url: "{{ route('lokaliti.update', $lokaliti->id) }}",
            method: 'POST', // POST + _method=PUT
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: formData,
            success: function () {
                alert('Lokaliti updated successfully!');
                window.location.href = "{{ route('lokaliti.index') }}";
            },
            error: function (xhr) {
                alert('Error updating Lokaliti!');
                console.error(xhr.responseText);
            }
        });
    });
});
</script>
@endpush
