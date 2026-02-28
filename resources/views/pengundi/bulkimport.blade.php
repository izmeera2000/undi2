@extends('layouts.app')

@section('title', 'Bulk Import')

@section('breadcrumb')
  @php
    $crumbs = [
      ['label' => 'Pengundi'],
      ['label' => 'Bulk Import', 'url' => route('pengundi.bulkimport')],
    ];
  @endphp
@endsection

@section('content')
  <div class="section">

    {{-- SUCCESS --}}
    <div id="successMsg" class="alert alert-success d-none"></div>

    {{-- ERRORS --}}
    <div id="errorMsg" class="alert alert-danger d-none"></div>

    <div class="row ">

      {{-- LEFT SIDE - FORM --}}
      <div class="col-lg-6">
        <div class="card shadow-sm border-0">
          <div class="card-header">
            <h5 class="mb-0"> Upload Pengundi CSV</h5>
          </div>

          <div class="card-body">
            <form id="importForm" enctype="multipart/form-data">
              @csrf

              <div class="mb-3">
                <label class="form-label fw-semibold">CSV File</label>
                <input class="form-control" type="file" name="file" required>
                <small class="text-muted">Only CSV format is allowed.</small>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Tahun Undian</label>
                <input type="number" name="tarikh_undian" class="form-control" placeholder="Contoh: 2022" required>
              </div>

              <h6 class="fw-bold mb-3">Data Validity Period</h6>

              <div class="mb-3">
                <label class="form-label fw-semibold">
                  Effective From
                </label>
                <input type="date" name="effective_from" class="form-control">
                <small class="text-muted">
                  Date when this Kod DM / Lokaliti structure started being used.
                </small>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">
                  Effective To
                </label>
                <input type="date" name="effective_to" class="form-control">
                <small class="text-muted">
                  Leave empty if this structure is still active.
                </small>
              </div>

              <div class="alert alert-info py-2">
                <strong>When should you fill this?</strong>
                <ul class="mb-0 mt-2">
                  <li>If constituency codes changed due to redelineation.</li>
                  <li>If importing historical election data (old PRU / PRN).</li>
                  <li>If DM or Lokaliti moved under different Parlimen / DUN.</li>
                </ul>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Jenis Pilihan Raya</label>
                <select name="pilihan_raya_type" class="form-select" required>
                  <option value="" disabled selected>Pilih Jenis Pilihan Raya</option>
                  <option value="PRU">PRU</option>
                  <option value="PRN">PRN</option>
                  <option value="PRK">PRK</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Nombor Siri Pilihan Raya</label>
                <input type="number" name="pilihan_raya_series" class="form-control" placeholder="Contoh: 15" required
                  min="1">
              </div>

              <button type="submit" class="btn btn-success w-100" id="submitBtn">
                Upload & Import
              </button>
            </form>

            {{-- Loading --}}
            <div id="loading" class="mt-3 d-none text-center">
              <div class="spinner-border spinner-border-sm text-success"></div>
              <span class="ms-2">Importing… please wait</span>
            </div>

            {{-- Import Progress --}}
            <div class="progress mt-3 d-none" id="importProgressWrapper">
              <div id="importProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                style="width:0%">0%</div>
            </div>

            {{-- Transfer Progress --}}
            <div class="progress mt-2 d-none" id="transferProgressWrapper">
              <div id="transferProgressBar" class="progress-bar bg-info progress-bar-striped progress-bar-animated"
                style="width:0%">0%</div>
            </div>

          </div>
        </div>
      </div>


      {{-- RIGHT SIDE - INSTRUCTIONS --}}
      <div class="col-lg-6">

        {{-- Instruction Card --}}
        <div class="card shadow-sm border-0 mb-4">
          <div class="card-header ">
            <h5 class="mb-0">Import Instructions</h5>
          </div>
          <div class="card-body">
            <ul class="mb-0">
              <li>File must be in <strong>CSV format</strong> (.csv).</li>
              <li>First row must contain <strong>column headers</strong>.</li>
              <li>No empty rows in between data.</li>
              <li>Make sure IC numbers do not contain spaces or symbols.</li>
              <li>Large files will take time — do not refresh the page.</li>
              <li>Import process has 2 stages:
                <ul>
                  <li>Stage 1: Import to temporary table</li>
                  <li>Stage 2: Transfer to main table</li>
                </ul>
              </li>
            </ul>
          </div>
        </div>

        {{-- Required Columns Card --}}
        <div class="card shadow-sm border-0">
          <div class="card-header">
            <h5 class="mb-0">Required CSV Columns</h5>
          </div>
          <div class="card-body">

            <div class="alert alert-warning">
              ⚠ Column names must match <strong>exactly</strong> (including spaces).
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
                      "KOD PAR",
                      "NAMAPAR",
                      "KOD DUN",
                      "NAMADUN",
                      "KODDM",
                      "NAMADM",
                      "KODLOKALITI",
                      "NAMALOKALITI",
                      "NOKP BARU",
                      "NOKP LAMA",
                      "NAMA",
                      "ALAMAT SPR",
                      "BANGSA",
                      "BANGSA SPR",
                      "JANTINA",
                      "STATUS BARU",
                      "KODPAR PRU12",
                      "TAHUN LAHIR",
                      "UMUR",
                      "STATUS UMNO",
                      "ALAMAT JPN 1",
                      "ALAMAT JPN 2",
                      "ALAMAT JPN 3",
                      "POSKOD",
                      "BANDAR",
                      "NEGERI",
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

            <div class="mt-3">
              <strong>Important Notes:</strong>
              <ul class="mb-0 mt-2">
                <li>Header row must be the first row.</li>
                <li>No additional columns allowed.</li>
                <li>Do not rename headers.</li>
                <li>Spaces must match exactly (example: <code>NOKP BARU</code> not <code>NOKPBARU</code>).</li>
                <li>Encoding must be UTF-8.</li>
              </ul>
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

      fetch("{{ route('pengundi.import') }}", {
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
          errorMsg.innerText = data.error || 'Unknown error during import';
          errorMsg.classList.remove('d-none');
          loading.classList.add('d-none');
          submitBtn.disabled = false;
        });

    });
  </script>

@endsection