@extends('layouts.app')

@section('title', 'Analytics')



@section('breadcrumb')
  @php
    // Build dynamic crumbs based on request
    $crumbs = [
      ['label' => 'Pengundi', 'url' => route('pengundi.analysis')],
      ['label' => 'Analytics', 'url' => route('pengundi.analysis')],
    ];

  @endphp

@endsection


@section('content')


  <div class="mb-4">
    <div
      class="form-actions-bar d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">


      <!-- Controls -->
      <div class="form-actions-buttons d-flex flex-column flex-md-row gap-2 w-100 w-md-auto">
        <!-- Selects stack vertically on small screens -->
        <div class="d-flex flex-column flex-md-row gap-2 flex-grow-1">
          <select id="modeSelect" class="form-select form-select-lg {{ $years->count() <= 1 ? 'd-none' : '' }}">
            <option value="single" selected>Single Year</option>
            <option value="compare">Compare Years</option>
          </select>

          <select id="year1" class="form-select form-select-lg">
            @foreach($years as $year)
              <option value="{{ $year }}" {{ $loop->first ? 'selected' : '' }}>
                {{ $year }}
              </option>
            @endforeach
          </select>

          <select id="year2" class="form-select form-select-lg d-none {{ $years->count() <= 1 ? 'd-none' : '' }}">
            @foreach($years as $year)
              <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
          </select>
        </div>

        <!-- Button: full width on small screens -->
        <button id="exportPdf" class="btn btn-danger btn-lg  w-md-auto mt-2 mt-md-0">
          Export PDF
        </button>
      </div>

    </div>
  </div>




  <!-- Stats Row -->
  <div class="dashboard-grid dashboard-grid-3">
    <!-- Total Visitors -->
    <div class="card widget-stat">
      <div class="widget-stat-header">
        <div>
          <div class="widget-stat-value" id="totalPengundi">248,532</div>
          <div class="widget-stat-label">Jumlah Pengundi</div>
        </div>
        <div class="widget-stat-icon primary">
          <i class="bi bi-people"></i>
        </div>
      </div>
      <div id="totalPengundib">

      </div>

    </div>



    <!-- Bounce Rate -->
    <div class="card widget-stat">
      <div class="widget-stat-header">
        <div>
          <div class="widget-stat-value" id="totalFirstTime">32.4%</div>
          <div class="widget-stat-label">First Time Voter</div>
        </div>
        <div class="widget-stat-icon warning">
          <i class="bi bi-person-check"></i>
        </div>
      </div>
      <div id="totalFirstTimeb">

      </div>
      {{-- <div class="widget-stat-change positive">
        <i class="bi bi-arrow-down"></i> 5.2% vs last month
      </div> --}}
    </div>

    <!-- Avg Session Duration -->
    <div class="card widget-stat">
      <div class="widget-stat-header">
        <div>
          <div class="widget-stat-value" id="totalUmno">4m 32s</div>
          <div class="widget-stat-label">Pengundi UMNO</div>
        </div>
        <div class="widget-stat-icon danger">
          <i class="umno-logo2">
            @include('layouts.logo')

          </i>
        </div>
      </div>
      <div id="totalUmnob">

      </div>

    </div>
  </div>




  <!-- Charts Row -->
  <div class="mb-4">
    <!-- Traffic Overview Chart -->
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Bangsa</h5>

      </div>
      <div class="card-body overflow-auto">
        <div class="chart-container" id="bangsaChart"></div>
      </div>
    </div>




  </div>

  <div class="row   mb-4">
    <!-- First Column: Jantina Chart 1 (7 units) -->
    <div class="col-md-6 col-12">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">Umur</h5>
        </div>
        <div class="card-body overflow-auto">
          <div class="chart-container" id="umurChart"></div>
        </div>
      </div>
    </div>

    <!-- Second Column: Jantina Chart 2 (5 units) -->
    <div class="col-md-6 col-12">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">Jantina</h5>
        </div>
        <div class="card-body  overflow-auto">
          <div class="chart-container" id="jantinaChart"></div>
        </div>
      </div>
    </div>
  </div>



  <div class="row   mb-4">


    <div class="col-md-6 col-12 ">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title">DUN Chart DunxUMNOxUmur </h5>
          <div>

          </div>
        </div>
        <div class="card-body overflow-auto">
          <div class="chart-container mx-auto" id="dunChart1"></div>
        </div>
      </div>
    </div>


    <div class="col-md-6 col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title">DUN Chart DunxUMNOxUmur </h5>
          <div>

          </div>
        </div>
        <div class="card-body overflow-auto">
          <div class="chart-container mx-auto" id="dunChart2"></div>
        </div>
      </div>
    </div>


  </div>


  <div class="col">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Negeri by UMNO and First Time</h5>
      </div>
      <div class="card-body  overflow-auto">
        <div class="chart-container" id="negeriChart"></div>
      </div>
    </div>
  </div>




  <!-- Bootstrap Tooltip Modal -->
  <div class="modal fade" id="tooltipModal" tabindex="-1" aria-labelledby="tooltipModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="tooltipModalLabel">Data Point Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="tooltipModalBody"></div>
      </div>
    </div>
  </div>



@endsection

@push('scripts')

  <script src="https://www.gstatic.com/charts/loader.js"></script>
  <script>
    google.charts.load('current', {
      packages: ['geochart']
    });
  </script>









  @include('pengundi.js.exportpdf')





  <script>
    const DashboardState = {
      cube: [],
      totals: {
        mode: 'single',
        data: []
      },
      charts: {
        bangsa: { chart: null },
        umur: { chart: null },
        jantina: { chart: null },



        negeri: { chart: null },


        dun1: { chart: null },
        dun2: { chart: null },

      }
    };


    async function loadDashboard(payload) {
      const cacheKey = 'dashboard_' + btoa(JSON.stringify(payload));
      const CACHE_TTL = 5 * 60 * 1000; // 5 minutes
      // console.log(payload);


      const cached = sessionStorage.getItem(cacheKey);


      if (cached) {
        const { data, expires } = JSON.parse(cached);
        if (Date.now() < expires) {
          // console.log('using cache');
          applyDashboardData(data);
          return;
        }
        sessionStorage.removeItem(cacheKey);
      }


      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

      const res = await fetch('/analytics/pengundi', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(payload)
      });

      const data = await res.json();
      // console.log('rendering (fresh)');


      sessionStorage.setItem(cacheKey, JSON.stringify({
        data,
        expires: Date.now() + CACHE_TTL
      }));

      applyDashboardData(data);
    }

    function applyDashboardData(data) {
      DashboardState.cube = data.cube;



      if (Array.isArray(data.totals)) {


        DashboardState.totals.mode =
          data.totals.length > 1 ? 'compare' : 'single';

        DashboardState.totals.data = data.totals.map(t => ({
          year: t.tarikh_undian,
          totalPengundi: t.total_pengundi,
          totalUmno: t.total_umno,
          totalFirstTime: t.total_first_time_voter
        }));

      }

      renderAll();
    }





    function renderAll() {

      renderKPIs(DashboardState.cube, DashboardState.totals);
      renderBangsaChart();
      renderUmurChart(DashboardState.cube);
      renderJantinaChart(DashboardState.cube);
      renderDmUmurChart(DashboardState.cube);
      renderNegeriChart(DashboardState.cube);


      // renderMalaysiaGeoChart(DashboardState.cube);
    }

  </script>

  @include('pengundi.charts.negeri')




  @include('pengundi.charts.bangsa')



  @include('pengundi.charts.umur')



  @include('pengundi.charts.jantina')





  @include('pengundi.charts.dmumur')


  <script>

    function renderKPIs(cube, totals) {

      if (!totals || !totals.data || totals.data.length === 0) return;


      const elPengundi = document.getElementById('totalPengundi');
      const elUmno = document.getElementById('totalUmno');
      const elFirstTime = document.getElementById('totalFirstTime');

      const elPengundib = document.getElementById('totalPengundib');
      const elUmnob = document.getElementById('totalUmnob');
      const elFirstTimeb = document.getElementById('totalFirstTimeb');



      // Helper to calculate percentage change
      const percentChange = (current, previous) => {
        if (!previous || previous === 0) return "0.000";
        return (((current - previous) / previous) * 100).toFixed(3);
      };

      // 🔹 SINGLE MODE
      if (totals.mode === 'single') {

        const t = totals.data[0];

        elPengundi.innerHTML = `
                                        ${t.totalPengundi.toLocaleString()}
                                      `;

        elUmno.innerHTML = `
                                        ${t.totalUmno.toLocaleString()}
                                      `;

        elFirstTime.innerHTML = `
                                        ${t.totalFirstTime.toLocaleString()}
                                      `;


        elPengundib.innerHTML = '';
        elUmnob.innerHTML = '';
        elFirstTimeb.innerHTML = '';
      }

      // 🔥 COMPARE MODE
      else if (totals.mode === 'compare' && totals.data.length >= 2) {

        // sort by year (ascending)
        const sorted = [...totals.data].sort((a, b) => a.year - b.year);

        const previous = sorted[0];
        const current = sorted[1];

        const pChange = percentChange(current.totalPengundi, previous.totalPengundi);
        const uChange = percentChange(current.totalUmno, previous.totalUmno);
        const fChange = percentChange(current.totalFirstTime, previous.totalFirstTime);

        const buildHTML = (value, change) => {
          const isPositive = change >= 0;
          const icon = isPositive ? 'bi-arrow-up' : 'bi-arrow-down';
          const className = isPositive ? 'positive' : 'negative';

          return `

                                      <div class="widget-stat-change ${className}">
                                        <i class="bi ${icon}"></i>
                                        ${Math.abs(change).toFixed(1)}% vs ${previous.year}
                                      </div>
                                    `;
        };

        elPengundib.innerHTML = buildHTML(current.totalPengundi, pChange);
        elUmnob.innerHTML = buildHTML(current.totalUmno, uChange);
        elFirstTimeb.innerHTML = buildHTML(current.totalFirstTime, fChange);
      }
    }

  </script>

  <script>

    document.addEventListener('DOMContentLoaded', () => {

      const modeSelect = document.getElementById('modeSelect');
      const year1Select = document.getElementById('year1');
      const year2Select = document.getElementById('year2');

      // ----------------------------
      // Compare Mode Logic
      // ----------------------------
      function updateCompareMode() {

        if (modeSelect.value === 'compare') {

          year2Select.classList.remove('d-none');

          const selectedYear1 = parseInt(year1Select.value);
          const options = [...year2Select.options].map(o => parseInt(o.value));

          // Auto pick different year (prefer highest other year)
          const autoYear = options
            .filter(y => y !== selectedYear1)
            .sort((a, b) => b - a)[0];

          if (autoYear) {
            year2Select.value = autoYear;
          }

        } else {
          year2Select.classList.add('d-none');
        }
      }

      function preventSameYear() {
        if (modeSelect.value === 'compare' &&
          year1Select.value === year2Select.value) {

          const alternative = [...year2Select.options]
            .find(opt => opt.value !== year1Select.value);

          if (alternative) {
            year2Select.value = alternative.value;
          }
        }
      }

      // ----------------------------
      // Load Dashboard
      // ----------------------------
      function onFilterChange() {

        updateCompareMode();
        preventSameYear();

        loadDashboard({
          year1: year1Select.value,
          year2: modeSelect.value === 'compare' ? year2Select.value : null,
          mode: modeSelect.value,
        });
      }

      // ----------------------------
      // Events
      // ----------------------------
      modeSelect.addEventListener('change', onFilterChange);
      year1Select.addEventListener('change', onFilterChange);
      year2Select.addEventListener('change', onFilterChange);

      // Initial load
      onFilterChange();

    });



    function lightenColor(hex, factor = 0.5) {
      const r = parseInt(hex.substr(1, 2), 16);
      const g = parseInt(hex.substr(3, 2), 16);
      const b = parseInt(hex.substr(5, 2), 16);

      const newR = Math.round(r + (255 - r) * factor);
      const newG = Math.round(g + (255 - g) * factor);
      const newB = Math.round(b + (255 - b) * factor);

      return `rgb(${newR}, ${newG}, ${newB})`;
    }

    // Ensure chartRef exists
    function ensureChartRef(ref) {
      if (!ref || typeof ref !== "object") return { chart: null };
      return ref;
    }

    // Destroy chart safely
    function destroyChart(chartRef) {
      if (chartRef.chart) {
        chartRef.chart.destroy();
        chartRef.chart = null;
      }
    }









  </script>



@endpush