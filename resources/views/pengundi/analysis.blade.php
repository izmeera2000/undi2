@extends('layouts.app')

@section('title', 'Dashboard')


@section('content')

  <div class="mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Overview</h5>

        <div class="d-flex gap-2">
          <!-- Mode -->
          <select id="modeSelect" class="form-select form-select-sm {{ $years->count() <= 1 ? 'd-none' : '' }}">
            <option value="single" selected>Single Year</option>
            <option value="compare">Compare Years</option>
          </select>


          <!-- Year 1 -->
          <select id="year1" class="form-select form-select-sm">
            @foreach($years as $year)
              <option value={{ $year }} {{ $loop->first ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
          </select>

          <!-- Year 2 (hidden unless compare) -->
          <select id="year2" class="form-select form-select-sm {{ $years->count() <= 1 ? 'd-none' : '' }}">
            @foreach($years as $year)
              <option value={{ $year }}>{{ $year }}</option>
            @endforeach
          </select>

          <button id="exportPdf" class="btn btn-danger">
            Export PDF
          </button>

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
          <div class="widget-stat-value" id="totalPengundi">248,532</div>
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
          <div class="widget-stat-value" id="totalFirstTime">32.4%</div>
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
          <div class="widget-stat-value" id="totalUmno">4m 32s</div>
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
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">Bangsa</h5>

      </div>
      <div class="card-body">
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
        <div class="card-body">
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
        <div class="card-body">
          <div class="chart-container" id="jantinaChart"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row  mb-4">
    <!-- First Column: Ahli Umno Bar Chart (7 units) -->
    <div class="col-md-7">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">
            Ahli Umno Bar (active/nonactive) also pengundi / not by umur_group
          </h5>
        </div>
        <div class="card-body">
          <div class="chart-container" id="ahliChart2"></div>
        </div>
      </div>
    </div>

    <!-- Second Column: Ahli Umno Doughnut Chart (5 units) -->
    <div class="col-md-5">
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title mb-0">
            Ahli Umno Doughnut (active/nonactive) also pengundi / not
          </h5>
        </div>
        <div class="card-body">
          <div class="chart-container" id="ahliChart"></div>
        </div>
      </div>
    </div>
  </div>



  <div class="mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">DUN Chart (umur group)</h5>
        <div>
          <select id="dunSelect" class="form-select d-inline-block" style="width:200px;">
            <option value="">All</option>
            @foreach($duns as $dun)
              <option value="{{ $dun->namadun }}">{{ $dun->namadun }}</option>
            @endforeach
          </select>

          <button id="loadDunChart" class="btn btn-primary btn-sm">Load Chart</button>
        </div>
      </div>
      <div class="card-body">
        <div class="chart-container" id="dundmChartGrouped"></div>
      </div>
    </div>
  </div>


  <div class="mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">First Time Voters </h5>

      </div>
      <div class="card-body">
        <div class="chart-container" id="firsttimeChart"></div>
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


<script>


  const csrfToken = document.querySelector('meta[name="csrf-token"]').content;


  const modeSelect = document.getElementById('modeSelect');
  const year1Select = document.getElementById('year1');
  const year2Select = document.getElementById('year2');
  const dunSelect = document.getElementById('dunSelect');



  document.getElementById('exportPdf').addEventListener('click', async () => {
    // Map chart objects to friendly titles
    const charts = [
      { chart: overviewChart.chart, title: 'Overview Chart' },
      { chart: jantinaChart.chart, title: 'Jantina Chart' },
      { chart: jantinaChart2.chart, title: 'Jantina by Umur' },
      { chart: ahliChart.chart, title: 'Ahli UMNO Chart' },
      { chart: ahliChart2.chart, title: 'Ahli UMNO by Umur' },
      // { chart: dundmChart.chart, title: 'DUN DM Treemap' },
      { chart: dundmChartGrouped.chart, title: 'DUN DM Grouped by Umur' }
    ];

    const images = [];

    for (const { chart, title } of charts) {
      if (!chart) continue; // skip if not rendered yet
      try {
        const { imgURI } = await chart.dataURI();
        images.push({ id: chart.w.globals.chartID, image: imgURI, title }); // <-- include title
      } catch (err) {
        console.warn('Chart not ready for export:', chart.w.globals.chartID);
      }
    }

    if (!images.length) {
      alert('No charts ready for export yet.');
      return;
    }

    fetch('/pengundi/analytics/pdf', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ charts: images })
    })
      .then(res => res.blob())
      .then(blob => window.open(URL.createObjectURL(blob)));
  });







  const DashboardState = {
    cube: [],
    totals: {},
    charts: {
      bangsa: { chart: null },
      umur: { chart: null },
      jantina: { chart: null }
    }
  };


  async function loadDashboard(payload) {

    const res = await fetch('/analytics/pengundi', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify(payload)
    });

    const data = await res.json();
    console.log("rendering");

    DashboardState.cube = data.cube;
    DashboardState.totals = {
      totalPengundi: data.total_pengundi,
      totalUmno: data.total_umno,
      totalFirstTime: data.total_first_time_voter
    };

    renderAll();
  }


  function renderAll() {
    renderKPIs(DashboardState.cube, DashboardState.totals);
    renderBangsaChart(DashboardState.cube);
    renderUmurChart(DashboardState.cube);
    renderJantinaChart(DashboardState.cube);
  }



function renderBangsaChart(cube) {
  const categories = ['18-20','21-29','30-39','40-49','50-59','60+']; // X-axis = Umur
  const bangsaGroups = ['Melayu', 'Cina', 'India', 'Lain-lain'];      // Stacks within each Umur

  // Create series for UMNO and Bukan UMNO for each Bangsa
  const series = bangsaGroups.flatMap(bangsa => [
    {
      name: `UMNO - ${bangsa}`,
      data: categories.map(umur =>
        cube
          .filter(x =>
            x.umur_group === umur &&
            x.bangsa_group === bangsa &&
            x.status_umno === '1'
          )
          .reduce((sum, x) => sum + x.total, 0)
      )
    },
    {
      name: `Bukan UMNO - ${bangsa}`,
      data: categories.map(umur =>
        cube
          .filter(x =>
            x.umur_group === umur &&
            x.bangsa_group === bangsa &&
            x.status_umno === '0'
          )
          .reduce((sum, x) => sum + x.total, 0)
      )
    }
  ]);

  renderStackedBar(
    document.querySelector('#bangsaChart'),
    DashboardState.charts.bangsa,
    categories,
    series,
    'Jumlah Pengundi',           // Y-axis
    'Umur',                       // X-axis
    [],                           // optional colors
    'Umur × Bangsa × Status UMNO' // Chart title
  );
}



  function renderUmurChart(cube) {
    const categories = ['18-20', '21-29', '30-39', '40-49', '50-59', '60+'];

    // All combinations of status_umno × status_baru
    const statusCombinations = [
      { umno: '1', baru: '1', label: 'UMNO - First Time' },
      { umno: '1', baru: '0', label: 'UMNO - Existing' },
      { umno: '0', baru: '1', label: 'Bukan UMNO - First Time' },
      { umno: '0', baru: '0', label: 'Bukan UMNO - Existing' },
    ];

    const series = statusCombinations.map(combo => ({
      name: combo.label,
      data: categories.map(group =>
        cube
          .filter(x =>
            x.umur_group === group &&
            x.status_umno === combo.umno &&
            x.status_baru === combo.baru
          )
          .reduce((sum, x) => sum + x.total, 0)
      )
    }));

    renderStackedBar(
      document.querySelector('#umurChart'),
      DashboardState.charts.umur,
      categories,
      series,
      'Jumlah Pengundi', // Y-axis
      'Umur',            // X-axis
      [],                // optional colors
      'Umur × Status UMNO × First Time Voter'
    );
  }





  function renderJantinaChart(cube) {
    const categories = ['Lelaki', 'Perempuan'];

    const series = [
      {
        name: 'UMNO',
        data: categories.map(group =>
          cube
            .filter(x => x.jantina2 === group && x.status_umno === '1')
            .reduce((sum, x) => sum + x.total, 0)
        )
      },
      {
        name: 'Bukan UMNO',
        data: categories.map(group =>
          cube
            .filter(x => x.jantina2 === group && x.status_umno === '0')
            .reduce((sum, x) => sum + x.total, 0)
        )
      }
    ];

    renderStackedBar(
      document.querySelector('#jantinaChart'),
      DashboardState.charts.jantina, // chart ref
      categories,
      series,
      'Jumlah Pengundi', // Y-axis
      'Jantina',            // X-axis
      [],                // Colors optional
      'Jantina × Status UMNO' // Chart title
    );
  }










  function renderKPIs(cube, totals) {
    document.getElementById('totalPengundi').innerHTML =
      totals.totalPengundi.toLocaleString();

    document.getElementById('totalUmno').innerHTML =
      totals.totalUmno.toLocaleString();

    document.getElementById('totalFirstTime').innerHTML =
      totals.totalFirstTime.toLocaleString();
  }


  function onFilterChange() {
    loadDashboard({
      year1: year1Select.value,
      year2: year2Select.value,
      mode: modeSelect.value,
      dun: dunSelect.value
    });
  }
  document.addEventListener('DOMContentLoaded', () => {
    // Load dashboard on first visit
    onFilterChange();

    // Optional: attach event listeners
    modeSelect.addEventListener('change', onFilterChange);
    year1Select.addEventListener('change', onFilterChange);
    year2Select.addEventListener('change', onFilterChange);
    dunSelect.addEventListener('change', onFilterChange);
  });


</script>



@endpush