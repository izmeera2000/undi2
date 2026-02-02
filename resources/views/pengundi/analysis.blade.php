@extends('layouts.app')

@section('title', 'Dashboard')


@section('content')


  <div class="mb-4">
    <!-- Traffic Overview Chart -->
    <div>
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Overview</h5>

          <div class="d-flex gap-2">
            <!-- Mode -->
            <select id="modeSelect" class="form-select form-select-sm">
              <option value="single" selected>Single Year</option>
              <option value="compare">Compare Years</option>
            </select>

            <!-- Year 1 -->
            <select id="year1" class="form-select form-select-sm">
              <option value="2022" selected>2022</option>
              <option value="2025">2025</option>
              <option value="2024">2024</option>
            </select>

            <!-- Year 2 (hidden unless compare) -->
            <select id="year2" class="form-select form-select-sm d-none">

              <option value="2025">2025</option>
              <option value="2024">2024</option>
              <option value="2023">2023</option>
              <option value="2022">2022</option>

            </select>
          </div>
        </div>



      </div>
    </div>



  </div>

  <!-- Stats Row -->
  <div class="dashboard-grid dashboard-grid-3">
    <!-- Total Visitors -->
    <div class="card widget-stat">
      <div class="widget-stat-header">
        <div>
          <div class="widget-stat-value">248,532</div>
          <div class="widget-stat-label">Jumlah Pengundi</div>
        </div>
        <div class="widget-stat-icon primary">
          <i class="bi bi-people"></i>
        </div>
      </div>
      <div class="widget-stat-change positive">
        <i class="bi bi-arrow-up"></i> 24.5% vs last month
      </div>
    </div>



    <!-- Bounce Rate -->
    <div class="card widget-stat">
      <div class="widget-stat-header">
        <div>
          <div class="widget-stat-value">32.4%</div>
          <div class="widget-stat-label">First Time Voter</div>
        </div>
        <div class="widget-stat-icon warning">
          <i class="bi bi-arrow-return-left"></i>
        </div>
      </div>
      <div class="widget-stat-change positive">
        <i class="bi bi-arrow-down"></i> 5.2% vs last month
      </div>
    </div>

    <!-- Avg Session Duration -->
    <div class="card widget-stat">
      <div class="widget-stat-header">
        <div>
          <div class="widget-stat-value">4m 32s</div>
          <div class="widget-stat-label">Ahli UMNO</div>
        </div>
        <div class="widget-stat-icon info">
          <i class="bi bi-clock-history"></i>
        </div>
      </div>
      <div class="widget-stat-change positive">
        <i class="bi bi-arrow-up"></i> 12.1% vs last month
      </div>
    </div>
  </div>




  <!-- Charts Row -->
  <div class="mb-4">
    <!-- Traffic Overview Chart -->
    <div>
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Overview</h5>

        </div>
        <div class="card-body">

        </div>
      </div>


    </div>


  </div>
  <div class="two-column-layout">


    <div class="mb-4">
      <!-- Traffic Overview Chart -->
      <div>
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">Jantina </h5>

          </div>
          <div class="card-body">
            <div class="chart-container" id="jantinaChart2"></div>
          </div>
        </div>


      </div>


    </div>


    <div class="mb-4">
      <!-- Traffic Overview Chart -->
      <div>
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">Jantina </h5>

          </div>
          <div class="card-body">
                <x-analytics.chart id="jantinaDonut3" type="donut" endpoint="/analytics/chart/jantina" mode="single"
            :colors="['#FF69B4', '#1E90FF']" :x-axis="['field' => 'jantina']" :data-a="[
          ['label' => 'Perempuan', 'value' => 'total'],
          ['label' => 'Lelaki', 'value' => 'total'],
      ]" />
          </div>
        </div>


      </div>


    </div>



  </div>


  <!-- Charts Row -->
  <div class="mb-4">
    <!-- Traffic Overview Chart -->
    <div>
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Ahli Umno Doughtnut (active/nnonactive) also pengunndi / not</h5>

        </div>
        <div class="card-body">
          <div class="chart-container" id=""></div>
        </div>
      </div>


    </div>


  </div>
  <div class="mb-4">
    <!-- Traffic Overview Chart -->
    <div>
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Ahli Umno Bar (active/nnonactive) also pengunndi / not by umur_group</h5>

        </div>
        <div class="card-body">
          <div class="chart-container" id=""></div>
        </div>
      </div>


    </div>


  </div>


  <div class="mb-4">
    <!-- Traffic Overview Chart -->
    <div>
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">BY DUN and DM (umr group)</h5>

        </div>
        <div class="card-body">
          <div class="chart-container" id=""></div>
        </div>
      </div>


    </div>


  </div>


  <div class="mb-4">
    <!-- Traffic Overview Chart -->
    <div>
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">radial chart by dun </h5>

        </div>
        <div class="card-body">
          <div class="chart-container" id=""></div>
        </div>
      </div>


    </div>


  </div>

@endsection

@push('scripts')
  <script>
    const modeSelect = document.getElementById('modeSelect');
    const year1 = document.getElementById('year1');
    const year2 = document.getElementById('year2');

    function triggerAnalytics() {
      // show / hide compare year
      year2.classList.toggle('d-none', modeSelect.value !== 'compare');

      window.dispatchEvent(
        new CustomEvent('analytics:change', {
          detail: {
            mode: modeSelect.value,
            year1: year1.value,
            year2: year2.value
          }
        })
      );
    }

    // 🔥 CONNECT EVENTS
    modeSelect.addEventListener('change', triggerAnalytics);
    year1.addEventListener('change', triggerAnalytics);
    year2.addEventListener('change', triggerAnalytics);

    // 🔥 INITIAL LOAD
    triggerAnalytics();
  </script>



@endpush