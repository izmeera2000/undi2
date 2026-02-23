@extends('layouts.app')

@section('title', 'Analytics')



@section('breadcrumb')
  @php
    $crumbs = [
      ['label' => 'Pengundi'],
      ['label' => 'Analytics', 'url' => route('pengundi.analytics')],
    ];
  @endphp
@endsection


@section('content')



  <div class="mb-4">
    <div
      class="form-actions-bar d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">

      <div class="form-actions-buttons d-flex flex-column flex-md-row gap-2 w-100 w-md-auto">

        <div class="d-flex flex-column flex-md-row gap-2 flex-grow-1">

          {{-- MODE --}}
          <select id="modeSelect" class="form-select form-select-lg {{ $datas->count() <= 1 ? 'd-none' : '' }}">
            <option value="single" selected>Single Election</option>
            <option value="compare">Compare Elections</option>
          </select>

          {{-- ELECTION 1 --}}
          <select id="election1" class="form-select form-select-lg">
            @foreach($datas as $data)
              <option value="{{ $data->pilihan_raya_type }}|{{ $data->pilihan_raya_series }}" {{ $loop->first ? 'selected' : '' }}>
                {{ $data->pilihan_raya_type }}{{ $data->pilihan_raya_series }}
                ({{ $data->year }})
              </option>
            @endforeach
          </select>

          {{-- ELECTION 2 --}}
          <select id="election2" class="form-select form-select-lg d-none {{ $datas->count() <= 1 ? 'd-none' : '' }}">
            @foreach($datas as $data)
              <option value="{{ $data->pilihan_raya_type }}|{{ $data->pilihan_raya_series }}">
                {{ $data->pilihan_raya_type }}{{ $data->pilihan_raya_series }}
                ({{ $data->year }})
              </option>
            @endforeach
          </select>

        </div>

        {{-- EXPORT BUTTON --}}
        <button id="exportPdf" class="btn btn-danger btn-lg w-md-auto mt-2 mt-md-0">
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

    @php
      // Array of charts, can be dynamic or passed from controller
      $charts = [
        ['id' => 'dunChart1', 'title' => 'DUN Chart DunxUMNOxUmur'],
        ['id' => 'dunChart2', 'title' => 'DUN Chart DunxUMNOxUmur'],
        // Add more charts here if needed
      ];
    @endphp

    @foreach($charts as $chart)
      <div class="col-md-6 col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">{{ $chart['title'] }}</h5>
            <div>
              {{-- Optional header actions --}}
            </div>
          </div>
          <div class="card-body overflow-auto">
            <div class="chart-container mx-auto" id="{{ $chart['id'] }}"></div>
          </div>
        </div>
      </div>
    @endforeach


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
      },
      // -----------------------------
      // Chart datasets
      // -----------------------------
      bangsaChart1: [],
      bangsaChart2: [],
      negeriChart1: [],
      negeriChart2: [],
      dmUmurChart1: [],
      dmUmurChart2: [],
      jantinaChart1: [],
      jantinaChart2: [],
      umurChart1: [],
      umurChart2: [],
    };


    async function loadDashboard(payload) {
      const cacheKey = 'dashboard_' + btoa(JSON.stringify(payload));
      const CACHE_TTL = 5 * 60 * 1000; // 5 minutes
      console.log(payload);


      const cached = sessionStorage.getItem(cacheKey);


      if (cached) {
        const { data, expires } = JSON.parse(cached);
        if (Date.now() < expires) {
          console.log('using cache');
          applyDashboardData(data);
          return;
        }
        sessionStorage.removeItem(cacheKey);
      }


      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
      try {
        const res = await fetch('{{ route('pengundi.analytics_data') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify(payload)
        });

        // Check HTTP status
        if (!res.ok) {

          if (res.status === 419) {
            console.warn('Session expired. Reloading...');
            window.location.reload(); // refresh page to get new CSRF token
            return;
          }

          const errorText = await res.text();
          console.error('HTTP Error:', res.status, res.statusText);
          console.error('Response body:', errorText);
          return;
        }

        const data = await res.json();
        console.log('Success:', data);
        applyDashboardData(data, payload);



        sessionStorage.setItem(cacheKey, JSON.stringify({
          data,
          expires: Date.now() + CACHE_TTL
        }));

      } catch (error) {
        console.error('Fetch Error:', error);
      }






    }
    function applyDashboardData(data, payload) {
      DashboardState.bangsaChart1 = Array.isArray(data.bangsaChart1) ? data.bangsaChart1 : [];
      DashboardState.bangsaChart2 = Array.isArray(data.bangsaChart2) ? data.bangsaChart2 : [];
      DashboardState.negeriChart1 = Array.isArray(data.negeriChart1) ? data.negeriChart1 : [];
      DashboardState.negeriChart2 = Array.isArray(data.negeriChart2) ? data.negeriChart2 : [];
      DashboardState.dmUmurChart1 = Array.isArray(data.dmUmurChart1) ? data.dmUmurChart1 : [];
      DashboardState.dmUmurChart1 = Array.isArray(data.dmUmurChart1) ? data.dmUmurChart1 : [];
      DashboardState.jantinaChart1 = Array.isArray(data.jantinaChart1) ? data.jantinaChart1 : [];
      DashboardState.jantinaChart2 = Array.isArray(data.jantinaChart2) ? data.jantinaChart2 : [];
      DashboardState.umurChart1 = Array.isArray(data.umurChart1) ? data.umurChart1 : [];
      DashboardState.umurChart2 = Array.isArray(data.umurChart2) ? data.umurChart2 : [];
      // If you have totals object

      if (data.totals1) {
        renderKPIs(data.totals1, data.totals2);
      }
      // After assigning, render charts
      renderAll(payload);
    }







    function renderAll(payload) {

      renderDmUmurChart(payload);

      renderBangsaChart(payload);
      renderUmurChart(payload);

      renderJantinaChart(payload);
      
      renderNegeriChart(payload);


    }




  </script>



  @include('pengundi.charts.negeri')

  @include('pengundi.charts.dmumur')

  @include('pengundi.charts.bangsa')
  @include('pengundi.charts.umur')
  @include('pengundi.charts.jantina')



  <script>
    function renderKPIs(totals1, totals2 = null) {
      console.log('start');

      if (!totals1) return;

      const elPengundi = document.getElementById('totalPengundi');
      const elUmno = document.getElementById('totalUmno');
      const elFirstTime = document.getElementById('totalFirstTime');

      const elPengundib = document.getElementById('totalPengundib');
      const elUmnob = document.getElementById('totalUmnob');
      const elFirstTimeb = document.getElementById('totalFirstTimeb');

      // Helper to calculate percentage change
      const percentChange = (current, previous) => {
        if (!previous || previous === 0) return 0;
        return ((current - previous) / previous) * 100;
      };

      // 🔹 SINGLE MODE
      if (!totals2) {
        elPengundi.innerHTML = totals1.total_pengundi.toLocaleString();
        elUmno.innerHTML = totals1.total_umno.toLocaleString();
        elFirstTime.innerHTML = totals1.total_first_time.toLocaleString();

        elPengundib.innerHTML = '';
        elUmnob.innerHTML = '';
        elFirstTimeb.innerHTML = '';
      }

      // 🔥 COMPARE MODE
      else {
        const previous = totals1;
        const current = totals2;

        const pChange = percentChange(current.total_pengundi, previous.total_pengundi);
        const uChange = percentChange(current.total_umno, previous.total_umno);
        const fChange = percentChange(current.total_first_time, previous.total_first_time);

        const buildHTML = (value, change) => {
          const isPositive = change >= 0;
          const icon = isPositive ? 'bi-arrow-up' : 'bi-arrow-down';
          const className = isPositive ? 'positive' : 'negative';

          return `<div class="widget-stat-change ${className}">
                            <i class="bi ${icon}"></i>
                            ${Math.abs(change).toFixed(1)}% vs previous
                        </div>`;
        };

        elPengundib.innerHTML = buildHTML(current.total_pengundi, pChange);
        elUmnob.innerHTML = buildHTML(current.total_umno, uChange);
        elFirstTimeb.innerHTML = buildHTML(current.total_first_time, fChange);

        // Update main KPI values to current
        elPengundi.innerHTML = current.total_pengundi.toLocaleString();
        elUmno.innerHTML = current.total_umno.toLocaleString();
        elFirstTime.innerHTML = current.total_first_time.toLocaleString();
      }

      console.log('rendered');
    }
  </script>



  <script>



    $(document).ready(function () {

      const $modeSelect = $('#modeSelect');
      const $election1Select = $('#election1');
      const $election2Select = $('#election2');

      // ----------------------------
      // Helper: Extract Series Number
      // ----------------------------
      function getSeries(value) {
        if (!value) return null;
        const parts = value.split('|'); // PRU|15
        return parseInt(parts[1]);
      }

      // ----------------------------
      // Compare Mode Logic
      // ----------------------------
      function updateCompareMode() {

        if ($modeSelect.val() === 'compare') {

          $election2Select.removeClass('d-none');

          const selectedElection1 = $election1Select.val();
          const selectedSeries1 = getSeries(selectedElection1);

          const options = $election2Select.find('option').map(function () {
            return {
              value: $(this).val(),
              series: getSeries($(this).val())
            };
          }).get();

          // Auto pick highest different series
          const autoElection = options
            .filter(opt => opt.value !== selectedElection1)
            .sort((a, b) => b.series - a.series)[0];

          if (autoElection) {
            $election2Select.val(autoElection.value);
          }

        } else {
          $election2Select.addClass('d-none');
        }
      }

      function preventSameElection() {

        if (
          $modeSelect.val() === 'compare' &&
          $election1Select.val() === $election2Select.val()
        ) {

          const alternative = $election2Select.find('option').filter(function () {
            return $(this).val() !== $election1Select.val();
          }).first();

          if (alternative.length) {
            $election2Select.val(alternative.val());
          }
        }
      }

      // ----------------------------
      // Load Dashboard
      // ----------------------------
      function onFilterChange() {

        updateCompareMode();
        preventSameElection();

        const election1 = $election1Select.val();
        const election2 = $modeSelect.val() === 'compare'
          ? $election2Select.val()
          : null;

        // Split before sending to backend
        const [type1, series1] = election1.split('|');
        let type2 = null;
        let series2 = null;

        if (election2) {
          [type2, series2] = election2.split('|');
        }

        loadDashboard({
          type1: type1,
          series1: series1,
          type2: type2,
          series2: series2,
          mode: $modeSelect.val(),
        });
      }

      // ----------------------------
      // Events
      // ----------------------------
      $modeSelect.on('change', onFilterChange);
      $election1Select.on('change', onFilterChange);
      $election2Select.on('change', onFilterChange);

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