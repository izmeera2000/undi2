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
                        <input type="text" name="kod_dun" class="form-control" value="{{ $dun->kod_dun }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Dun</label>
                        <input type="text" name="nama_dun" class="form-control" value="{{ $dun->nama_dun }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Parlimen</label>
                        <select name="parlimen_id" class="form-control" required>
                            @foreach($parlimens as $parlimen)
                                <option value="{{ $parlimen->id }}" {{ $dun->parlimen_id == $parlimen->id ? 'selected' : '' }}>
                                    {{ $parlimen->nama_par }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control" required>
                            <option value="active" {{ $dun->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $dun->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Effective From</label>
                        <input type="date" name="effective_from" class="form-control"
                            value="{{ $dun->effective_from ? $dun->effective_from->format('Y-m-d') : '' }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Effective To</label>
                        <input type="date" name="effective_to" class="form-control"
                            value="{{ $dun->effective_to ? $dun->effective_to->format('Y-m-d') : '' }}">
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
        $(document).ready(function() {
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
                        toastr.sucess('Dun updated successfully!');
                        window.location.href = "{{ route('dun.index') }}";
                    },
                    error: function (xhr) {
                        toastr.error('Error updating dun!');
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
@endpush