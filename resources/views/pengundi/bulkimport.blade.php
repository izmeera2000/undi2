@extends('layouts.app')

@section('title', 'Bulk Import')

@section('breadcrumb')
  @php
    $crumbs = [
      ['label' => 'Pengundi', 'url' => route('pengundi.analysis')],
      ['label' => 'Bulk Import', 'url' => route('pengundi.bulkimport')],
    ];
  @endphp
@endsection

@section('content')
  <div class="row g-4 mb-4">

    {{-- SUCCESS --}}
    <div id="successMsg" class="alert alert-success d-none"></div>

    {{-- ERRORS --}}
    <div id="errorMsg" class="alert alert-danger d-none"></div>

    <form id="importForm" enctype="multipart/form-data">
      @csrf
      <div class="mb-3">
        <label for="basicFile" class="form-label">CSV File</label>
        <input class="form-control" type="file" id="basicFile" name="file">
      </div>

      <input type="number" name="tarikh_undian" class="form-control mt-2" placeholder="Tahun Undian (contoh: 2022)"
        required>

      {{-- Optional effective dates --}}
      <input type="date" name="effective_from" class="form-control mt-2" placeholder="Effective From">
      <input type="date" name="effective_to" class="form-control mt-2" placeholder="Effective To">

      <button type="submit" class="btn btn-success mt-2" id="submitBtn">Upload CSV</button>
    </form>

    <div id="loading" class="mt-3 d-none">
      <div class="spinner-border spinner-border-sm"></div>
      Importing… please wait
    </div>


    <div class="progress mt-2 d-none" id="importProgressWrapper">
      <div id="importProgressBar" class="progress-bar" style="width:0%">0</div>
    </div>

    <div class="progress mt-2 d-none" id="transferProgressWrapper">
      <div id="transferProgressBar" class="progress-bar bg-info" style="width:0%">0</div>
    </div>

  </div>

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
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
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

      // Poll both import and transfer progress
      let polling = false;
      let interval = setInterval(async () => {
        if (polling) return;
        polling = true;

        try {
          // Import progress
          const importRes = await fetch("{{ route('pengundi.importProgress') }}");
          const importData = await importRes.json();

          if (importData.total > 0) {
            let importPercent = Math.round((importData.count / importData.total) * 100);
            importBar.style.width = importPercent + '%';
            importBar.innerText = importPercent + '%';
          }

          // Only fetch transfer progress if import is complete
          if (importData.count >= importData.total) {
            const transferRes = await fetch("{{ route('pengundi.transferProgress') }}");
            const transferData = await transferRes.json();

            if (transferData.total > 0) {
              let transferPercent = Math.round((transferData.count / transferData.total) * 100);
              transferBar.style.width = transferPercent + '%';
              transferBar.innerText = transferPercent + '%';

              // Stop interval when transfer is complete
              if (transferPercent >= 100) clearInterval(interval);
            }
          }

        } catch (error) {
          console.error('Error during polling:', error);
        } finally {
          polling = false;
        }

      }, 1000);


    });
  </script>
@endsection