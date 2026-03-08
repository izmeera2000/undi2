@extends('layouts.app')

@section('title', 'Bulk Import Culaan Pengundi')

@section('breadcrumb')
    @php
        $crumbs = [
            ['label' => 'Culaan', 'url' => route('culaan.index')],
            ['label' => $culaan->name ?? 'Culaan', 'url' => route('culaan.show', $culaan->id ?? 0)],
            ['label' => 'Bulk Import'],
        ];
    @endphp
@endsection

@section('content')
<div class="section">

    {{-- SUCCESS / ERROR --}}
    <div id="successMsg" class="alert alert-success d-none"></div>
    <div id="errorMsg" class="alert alert-danger d-none"></div>

    <div class="row">

        {{-- LEFT: Import Form --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h5 class="mb-0">Upload Culaan CSV</h5>
                </div>
                <div class="card-body">
                    <form id="importForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="culaan_id" value="{{ $culaan->id ?? 0 }}">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">CSV File</label>
                            <input class="form-control" type="file" name="file" accept=".csv" required>
                            <small class="text-muted">Only CSV allowed.</small>
                        </div>

                        <button type="submit" class="btn btn-success w-100" id="submitBtn">
                            Upload & Queue Import
                        </button>
                    </form>

                    {{-- Loading --}}
                    <div id="loading" class="mt-3 d-none text-center">
                        <div class="spinner-border spinner-border-sm text-success"></div>
                        <span class="ms-2">Importing… please wait</span>
                    </div>

                    {{-- Import Progress --}}
                    <div class="progress mt-3 d-none" id="importProgressWrapper">
                        <div id="importProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%">0%</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT: Instructions --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header"><h5 class="mb-0">Instructions</h5></div>
                <div class="card-body">
                    <ul>
                        <li>CSV format only</li>
                        <li>Header row must match exact column names</li>
                        <li>No empty rows</li>
                        <li>Large files may take time; do not refresh the page</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header"><h5 class="mb-0">Required Columns</h5></div>
                <div class="card-body">
                    <div class="alert alert-warning">⚠ Column headers must match exactly</div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-striped">
                            <thead class="table-light">
                                <tr><th>No</th><th>Column Name</th></tr>
                            </thead>
                            <tbody>
                                @php
                                    $columns = [
                                        'KOD LOKALITI','LOKALITI','PM','NO SIRI','SALURAN',
                                        'NAMA','NO KP','JANTINA','UMUR','BANGSA',
                                        'KATEGORI PENGUNDI','STATUS PENGUNDI','STATUS CULAAN',
                                        'CAWANGAN','NO AHLI','ALAMAT','STATUS','KATEGORI'
                                    ];
                                @endphp
                                @foreach($columns as $index => $col)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><code>{{ $col }}</code></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- JS --}}
<script>
document.getElementById('importForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = e.target;
    const submitBtn = document.getElementById('submitBtn');
    const loading = document.getElementById('loading');
    const successMsg = document.getElementById('successMsg');
    const errorMsg = document.getElementById('errorMsg');
    const progressWrapper = document.getElementById('importProgressWrapper');
    const progressBar = document.getElementById('importProgressBar');

    submitBtn.disabled = true;
    loading.classList.remove('d-none');
    progressWrapper.classList.remove('d-none');
    successMsg.classList.add('d-none');
    errorMsg.classList.add('d-none');

    const formData = new FormData(form);

    fetch("{{ route('culaan.pengundi.import.store', $culaan->id ?? 0) }}", {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(res => res.json().then(data => ({ok: res.ok, data})))
    .then(({ok,data}) => {
        if (!ok) throw data;
        successMsg.innerText = data.success || "Queued import successfully!";
        successMsg.classList.remove('d-none');
        loading.classList.add('d-none');
        submitBtn.disabled = false;

        // Start polling progress
        const interval = setInterval(async () => {
            const res = await fetch("{{ route('culaan.pengundi.import.progress',$culaan->id ?? 0)  }}");
            const prog = await res.json();
            const pct = Math.round((prog.count / prog.total) * 100);
            progressBar.style.width = pct + '%';
            progressBar.innerText = pct + '%';
            if(prog.count >= prog.total) clearInterval(interval);
        }, 1000);

    })
    .catch(err => {
        errorMsg.innerText = err.error || 'Unknown error';
        errorMsg.classList.remove('d-none');
        loading.classList.add('d-none');
        submitBtn.disabled = false;
    });
});
</script>
@endsection