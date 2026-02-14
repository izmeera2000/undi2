@extends('layouts.app')

@section('title', 'Edit DM')

@section('breadcrumb')
    @php
        $crumbs = [
            ['label' => 'DM', 'url' => route('dm.index')],
            ['label' => 'Edit', 'url' => route('dm.edit', $dm->id)],
        ];
    @endphp
@endsection

@section('content')
<section class="section">
    <div class="card g-4 mb-4">
        <div class="card-header">
            <h5>Edit DM</h5>
        </div>
        <div class="card-body">
            <form id="editDmForm">

                <div class="mb-3">
                    <label class="form-label">Kod DM</label>
                    <input type="text" name="koddm" class="form-control"
                        value="{{ $dm->koddm }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama DM</label>
                    <input type="text" name="namadm" class="form-control"
                        value="{{ $dm->namadm }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">DUN</label>
                    <select name="dun_id" class="form-control" required>
                        @foreach($duns as $dun)
                            <option value="{{ $dun->id }}"
                                {{ $dm->dun_id == $dun->id ? 'selected' : '' }}>
                                {{ $dun->namadun }} ({{ $dun->kod_dun }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="_method" value="PUT">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('dm.index') }}" class="btn btn-light">Cancel</a>
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

    $('#editDmForm').submit(function (e) {
        e.preventDefault();

        const formData = $(this).serialize();

        $.ajax({
            url: "{{ route('dm.update', $dm->id) }}",
            method: 'POST', // POST + _method=PUT
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: formData,
            success: function () {
                alert('DM updated successfully!');
                window.location.href = "{{ route('dm.index') }}";
            },
            error: function (xhr) {
                alert('Error updating DM!');
                console.error(xhr.responseText);
            }
        });
    });
});
</script>
@endpush
