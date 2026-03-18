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
                        <label class="form-label">DUN</label>
                        <select name="kod_dun" class="form-control" required>
                            @foreach($duns as $dun)
                                <option value="{{ $dun->kod_dun }}" {{ $dm->kod_dun == $dun->kod_dun ? 'selected' : '' }}>
                                   {{ $dun->kod_dun }}
                                </option>
                            @endforeach
                        </select>
                    </div>



                    {{-- 1️⃣ Kod DM --}}
                    <div class="mb-3">
                        <label class="form-label">Kod DM (2 digits)</label>
                        <input type="text" name="kod_dm" id="kod_dm" class="form-control" maxlength="2" pattern="\d{2}"
                            value="{{ substr($dm->kod_dm, -2) }}" {{-- Take last 2 digits of full kod_dm --}} required>
                        <small class="text-muted">Enter 2 digits. Full DM code will be generated automatically.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama DM</label>
                        <input type="text" name="nama_dm" class="form-control" value="{{ $dm->nama_dm }}" required>
                    </div>



                    <!-- Effective Dates -->
                    <div class="mb-3">
                        <label class="form-label">Effective From</label>
                        <input type="date" name="effective_from" class="form-control"
                            value="{{ $dm->effective_from ? $dm->effective_from->format('Y-m-d') : '' }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Effective To</label>
                        <input type="date" name="effective_to" class="form-control"
                            value="{{ $dm->effective_to ? $dm->effective_to->format('Y-m-d') : '' }}">
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
        $(document).ready(function() {
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
                        toastr.success('DM updated successfully!');
                        window.location.href = "{{ route('dm.index') }}";
                    },
                    error: function (xhr) {
                        toastr.error('Error updating DM!');
                        // console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
@endpush