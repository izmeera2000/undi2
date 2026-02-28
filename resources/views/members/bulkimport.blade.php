@extends('layouts.app')

@section('title', 'Bulk Import Members')

@section('breadcrumb')
  @php
    $crumbs = [
      ['label' => 'Members'],
      ['label' => 'Bulk Import', 'url' => route('members.bulkimport')],
    ];
  @endphp
@endsection

@section('content')
  <div class="section">

    {{-- SUCCESS --}}
    <div id="successMsg" class="alert alert-success d-none"></div>

    {{-- ERRORS --}}
    <div id="errorMsg" class="alert alert-danger d-none"></div>

    <div class="row">

      {{-- LEFT SIDE - FORM --}}
      <div class="col-lg-6">
        <div class="card shadow-sm border-0">
          <div class="card-header">
            <h5 class="mb-0">Upload Members CSV</h5>
          </div>
          <div class="card-body">
            <form id="importForm" enctype="multipart/form-data">
              @csrf

              <div class="mb-3">
                <label class="form-label fw-semibold">CSV File</label>
                <input class="form-control" type="file" name="file" required>
                <small class="text-muted">Only CSV format is allowed.</small>
              </div>

              <button type="submit" class="btn btn-success w-100" id="submitBtn">
                Upload & Import
              </button>
            </form>

            {{-- Loading --}}
            <div id="loading" class="mt-3 d-none text-center">
              <div class="spinner-border spinner-border-sm text-success"></div>
              <span class="ms-2">Processing… please wait</span>
            </div>

            {{-- Import Progress --}}
            <div class="mt-3 d-none" id="importProgressWrapper">
              <label>Import Progress</label>
              <div class="progress">
                <div id="importProgressBar" class="progress-bar progress-bar-striped "
                  style="width:0%">0%</div>
              </div>
            </div>

            {{-- Transfer Progress --}}
            <div class="mt-3 d-none" id="transferProgressWrapper">
              <label>Transfer Progress</label>
              <div class="progress">
                <div id="transferProgressBar" class="progress-bar progress-bar-striped bg-info"
                  style="width:0%">0%</div>
              </div>
            </div>

          </div>
        </div>
      </div>

      {{-- RIGHT SIDE - INSTRUCTIONS --}}
      <div class="col-lg-6">
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header">
            <h5 class="mb-0">Import Instructions</h5>
          </div>
          <div class="card-body">
            <ul class="mb-0">
              <li>File must be in <strong>CSV format</strong> (.csv).</li>
              <li>First row must contain <strong>column headers</strong>.</li>
              <li>No empty rows in between data.</li>
              <li>Ensure IC numbers and member numbers are correct.</li>
              <li>Large files will take time — do not refresh the page.</li>
              <li>CSV headers must match exactly with the required columns.</li>
            </ul>
          </div>
        </div>

        <div class="card shadow-sm border-0">
          <div class="card-header">
            <h5 class="mb-0">Required CSV Columns</h5>
          </div>
          <div class="card-body">
            <div class="alert alert-warning">
              ⚠ Column names must match <strong>exactly</strong>.
            </div>
            <div class="table-responsive">
              <table class="table table-bordered table-sm table-striped">
                <thead class="table-light">
                  <tr>
                    <th>No</th>
                    <th>Column Name (Exact Header Required)</th>
                  </tr>
                </thead>
                <tbody>
                  @php
                    $columns = [
                      'KODBHGN',
                      'NAMABHGN',
                      'KODDUN',
                      'NAMADUN',
                      'KODCWGN',
                      'NAMACWGN',
                      'NO_AHLI',
                      'NOKPBARU',
                      'NOKPLAMA',
                      'NAMA',
                      'TAHUNLAHIR',
                      'UMUR',
                      'JANTINA',
                      'ALAMAT_1',
                      'ALAMAT_2',
                      'ALAMAT_3',
                      'BANGSA',
                      'KODDM',
                      'ALAMAT_JPN_1',
                      'ALAMAT_JPN_2',
                      'ALAMAT_JPN_3',
                      'POSKOD',
                      'BANDAR',
                      'NEGERI',
                    ];
                  @endphp

                  @foreach($columns as $index => $column)
                    <tr>
                      <td>{{ $index + 1 }}</td>
                      <td><code class="fs-5">{{ $column }}</code></td>
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

  {{-- SCRIPT --}}
  <script>
    document.getElementById('importForm').addEventListener('submit', function (e) {
      e.preventDefault();

      const form = e.target;
      const submitBtn = document.getElementById('submitBtn');
      const loading = document.getElementById('loading');
      const importWrapper = document.getElementById('importProgressWrapper');
      const importBar = document.getElementById('importProgressBar');
      const transferWrapper = document.getElementById('transferProgressWrapper');
      const transferBar = document.getElementById('transferProgressBar');
      const successMsg = document.getElementById('successMsg');
      const errorMsg = document.getElementById('errorMsg');

      submitBtn.disabled = true;
      loading.classList.remove('d-none');
      importWrapper.classList.remove('d-none');
      transferWrapper.classList.remove('d-none');
      successMsg.classList.add('d-none');
      errorMsg.classList.add('d-none');

      const formData = new FormData(form);

      // Start import
      fetch("{{ route('members.import') }}", {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
      })
        .then(response => response.json().then(data => {
          if (!response.ok) throw data;
          return data;
        }))
        .then(data => {
          if (data.success) {
            successMsg.innerText = data.success;
            successMsg.classList.remove('d-none');
          }
          loading.classList.add('d-none');
          submitBtn.disabled = false;
        })
        .catch(data => {
          console.error('Import Error:', data);
          errorMsg.innerText = data.error || 'Unknown error during import';
          errorMsg.classList.remove('d-none');
          loading.classList.add('d-none');
          submitBtn.disabled = false;
        });

      // Poll import progress every 1s
      const importInterval = setInterval(() => {
        fetch("{{ route('members.importProgress') }}")
          .then(res => res.json())
          .then(progress => {
            const pct = Math.round((progress.count / progress.total) * 100);
            importBar.style.width = pct + '%';
            importBar.innerText = pct + '%';
            if (progress.count >= progress.total) clearInterval(importInterval);
          });
      }, 1000);

      // Poll transfer progress every 1s
      const transferInterval = setInterval(() => {
        fetch("{{ route('members.transferProgress') }}")
          .then(res => res.json())
          .then(progress => {
            const pct = Math.round((progress.count / progress.total) * 100);
            transferBar.style.width = pct + '%';
            transferBar.innerText = pct + '%';
            if (progress.count >= progress.total) clearInterval(transferInterval);
          });
      }, 1000);

    });
  </script>

@endsection