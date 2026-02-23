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

                    {{-- 1️⃣ DM Selection at Top --}}
                    <div class="mb-3">
                        <label class="form-label">DM</label>
                        <select name="koddm" class="form-select" required>
                            @foreach($dms->unique('koddm') as $dm)
                                <option value="{{ $dm->koddm }}" {{ $lokaliti->koddm == $dm->koddm ? 'selected' : '' }}>
                                    {{ $dm->koddm }} 
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 2️⃣ Kod Lokaliti (3 digits input) --}}
                    <div class="mb-3">
                        <label class="form-label">Kod Lokaliti (3 digits)</label>
                        <input type="text" name="kod_lokaliti" class="form-control" maxlength="3" pattern="\d{3}"
                            value="{{ substr($lokaliti->kod_lokaliti, -3) }}" required>
                        <small class="text-muted">Enter 3 digits. Full Lokaliti code will be generated
                            automatically.</small>
                    </div>

                    {{-- 3️⃣ Nama Lokaliti --}}
                    <div class="mb-3">
                        <label class="form-label">Nama Lokaliti</label>
                        <input type="text" name="nama_lokaliti" class="form-control" value="{{ $lokaliti->nama_lokaliti }}"
                            required>
                    </div>

                    {{-- 4️⃣ Effective Dates --}}
                    <div class="mb-3">
                        <label class="form-label">Effective From</label>
                        <input type="date" name="effective_from" class="form-control"
                            value="{{ $lokaliti->effective_from }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Effective To</label>
                        <input type="date" name="effective_to" class="form-control"
                            value="{{ $lokaliti->effective_to}}">
                    </div>

                    {{-- 5️⃣ Hidden PUT method --}}
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
        $(document).ready(function() {
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
                         toastr.success('Lokaliti updated successfully!');
                        window.location.href = "{{ route('lokaliti.index') }}";
                    },
                    error: function (xhr) {
                         toastr.error('Error updating Lokaliti!');
                        // console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
@endpush