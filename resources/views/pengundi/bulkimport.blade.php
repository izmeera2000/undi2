@extends('layouts.app')

@section('title', 'Bulk Import')


@section('breadcrumb')
  @php
    // Build dynamic crumbs based on request
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

      <input type="file" name="file" required>

      <input type="number" name="tarikh_undian" class="form-control mt-2" placeholder="Tahun Undian (contoh: 2022)"
        required>

      <button type="submit" class="btn btn-success mt-2" id="submitBtn">
        Upload CSV
      </button>
    </form>


    <div id="loading" class="mt-3 d-none">
      <div class="spinner-border spinner-border-sm"></div>
      Importing… please wait
    </div>

    <div class="progress mt-2 d-none" id="progressWrapper">
      <div id="progressBar" class="progress-bar" style="width:0%">0</div>
    </div>

  </div>

  <script>
    document.getElementById('importForm').addEventListener('submit', function (e) {
      e.preventDefault(); // prevent page reload

      const form = e.target;
      const submitBtn = document.getElementById('submitBtn');
      const loading = document.getElementById('loading');
      const progressWrapper = document.getElementById('progressWrapper');
      const progressBar = document.getElementById('progressBar');
      const successMsg = document.getElementById('successMsg');
      const errorMsg = document.getElementById('errorMsg');

      submitBtn.disabled = true;
      loading.classList.remove('d-none');
      progressWrapper.classList.remove('d-none');
      successMsg.classList.add('d-none');
      errorMsg.classList.add('d-none');

      const formData = new FormData(form);

      fetch("{{ route('pengundi.import') }}", {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      })
        .then(response => {
          // parse JSON for both success and error
          return response.json().then(data => {
            if (!response.ok) {
              // HTTP error like 500
              throw data;
            }
            return data;
          });
        })
        .then(data => {
          // success
          if (data.success) {
            successMsg.innerText = data.success;
            successMsg.classList.remove('d-none');
          }
          loading.classList.add('d-none');
          submitBtn.disabled = false;
        })
        .catch(data => {
          // handle both controller-thrown errors and HTTP errors
          errorMsg.innerText = data.error || 'Unknown error during import';
          errorMsg.classList.remove('d-none');
          loading.classList.add('d-none');
          submitBtn.disabled = false;
        });


      let polling = false;

      let interval = setInterval(() => {
        if (polling) return;

        polling = true;

        fetch("{{ route('pengundi.import.progress') }}")
          .then(res => res.json())
          .then(p => {
            console.log(p);

            if (p.total > 0) {
              let percent = Math.round((p.count / p.total) * 100);
              progressBar.style.width = percent + '%';
              progressBar.innerText = percent + '%';

              if (percent >= 100) {
                clearInterval(interval);
              }
            }
          })
          .catch(err => {
            console.warn('Progress fetch failed, retrying…', err);
          })
          .finally(() => {
            polling = false;
          });
      }, 1000);



    });
  </script>
@endsection