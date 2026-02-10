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
      {{-- <div class="widget-stat-change positive">
        <i class="bi bi-arrow-up"></i> 24.5% vs last month
      </div> --}}
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
      {{-- <div class="widget-stat-change positive">
        <i class="bi bi-arrow-down"></i> 5.2% vs last month
      </div> --}}
    </div>

    <!-- Avg Session Duration -->
    <div class="card widget-stat">
      <div class="widget-stat-header">
        <div>
          <div class="widget-stat-value" id="totalUmno">4m 32s</div>
          <div class="widget-stat-label">Ahli UMNO</div>
        </div>
        <div class="widget-stat-icon danger">
          <i class="umno-logo2">
            @include('layouts.logo')

          </i>
        </div>
      </div>
      {{-- <div class="widget-stat-change positive">
        <i class="bi bi-arrow-up"></i> 12.1% vs last month
      </div> --}}
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





  <div class="mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">DUN Chart DunxUMNOxUmur </h5>
        <div>
          {{-- <select id="dunSelect" class="form-select d-inline-block" style="width:200px;">
            <option value="">All</option>
            @foreach($duns as $dun)
            <option value="{{ $dun->namadun }}">{{ $dun->namadun }}</option>
            @endforeach
          </select> --}}

          {{-- <button id="loadDunChart" class="btn btn-primary btn-sm">Load Chart</button> --}}
        </div>
      </div>
      <div class="card-body overflow-auto">
        <div class="chart-container mx-auto" id="dunChart"></div>
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


  {{-- <div class="col">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">Negeri From by UMNO and By geochart </h5>
      </div>
      <div class="card-body  overflow-auto">
        <div class="chart-container" id="geochart"></div>
      </div>
    </div>
  </div> --}}


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



  <script>

    // const NEGERI_TO_CODE = {
    //   'JOHOR': 'MY-01',
    //   'KEDAH': 'MY-02',
    //   'KELANTAN': 'MY-03',
    //   'MELAKA': 'MY-04',
    //   'NEGERI SEMBILAN': 'MY-05',
    //   'PAHANG': 'MY-06',
    //   'PULAU PINANG': 'MY-07',
    //   'PERAK': 'MY-08',
    //   'PERLIS': 'MY-09',
    //   'SELANGOR': 'MY-10',
    //   'TERENGGANU': 'MY-11',
    //   'SABAH': 'MY-12',
    //   'SARAWAK': 'MY-13',
    //   'WILAYAH PERSEKUTUAN KUALA LUMPUR': 'MY-14',
    //   'LABUAN': 'MY-15',
    //   'PUTRAJAYA': 'MY-16'
    // };

    // function renderMalaysiaGeoChart(cube) {
    //   google.charts.setOnLoadCallback(() => {

    //     // init all states with 0
    //     const stateTotals = {};
    //     Object.values(NEGERI_TO_CODE).forEach(code => {
    //       stateTotals[code] = 0;
    //     });

    //     // aggregate from cube
    //     cube.forEach(x => {
    //       const negeri = x.negeri?.trim().toUpperCase();
    //       const code = NEGERI_TO_CODE[negeri];

    //       if (!code) return;

    //       // 🔁 choose what you want to plot
    //       if (x.status_umno === '1') {
    //         stateTotals[code] += x.total;
    //       }
    //     });

    //     const rows = Object.entries(stateTotals).map(([code, total]) => [
    //       code,
    //       total
    //     ]);

    //     const data = google.visualization.arrayToDataTable([
    //       ['Negeri', 'Jumlah UMNO'],
    //       ...rows
    //     ]);

    //     const options = {
    //       region: 'MY',
    //       resolution: 'provinces',
    //       colorAxis: { colors: ['#fee2e2', '#991b1b'] },
    //       datalessRegionColor: '#e5e7eb',
    //       backgroundColor: '#ffffff',
    //       legend: { textStyle: { color: '#111827' } }
    //     };

    //     const chart = new google.visualization.GeoChart(
    //       document.getElementById('geochart')
    //     );

    //     chart.draw(data, options);
    //   });
    // }


    document.getElementById('exportPdf').addEventListener('click', async () => {
      // console.log('Exporting PDF');

      const toast = new ToastMagic();
      toast.info("Exporting", "Exporting to PDF");


      if (!DashboardState.charts) {
        alert('Charts not ready');
        return;
      }


      await new Promise(r => setTimeout(r, 300));

      const images = [];

      for (const { chart, title } of Object.values(DashboardState.charts)) {
        if (!chart) continue;
        // console.log(chart.core.w.config);

        const originalHeight = chart.core.w.config.chart.height;
        const originalWidth = chart.core.w.config.chart.width;
        const originalAnimated = chart.core.w.config.chart.animations.enabled;
        const totalCategories = chart.w.config.xaxis.categories.length;
        // console.log(originalAnimated);

        try {

          await chart.updateOptions({
            chart: {
              animations: { enabled: false },
            },
          });
          if (totalCategories >= 6) {
            try {
              await chart.updateOptions({
                chart: {
                  width: 600,
                },
              });
            } catch (error) {
              console.error('Error updating chart width:', error);
            }

            await new Promise(r => setTimeout(r, 1300));

          } else {
            await new Promise(r => setTimeout(r, 600));

          }

          // 3️⃣ Wait a moment for ApexCharts to render

          // 4️⃣ Capture image
          const { imgURI } = await chart.dataURI({ scale: 2 });
          images.push({
            id: chart.w.globals.chartID,
            image: imgURI,
            title,
          });

          if (totalCategories >= 6) {

            await chart.updateOptions({
              chart: {
                width: originalWidth,
                animations: { enabled: originalAnimated },

              },
            });
          }


        } catch (err) {
          console.warn('Chart not ready:', chart?.w?.globals?.chartID, err);
        }
      }

      if (!images.length) {
        alert('No charts ready for export yet.');
        return;
      }
      // console.log("start ex  ");

      // 6️⃣ Send to backend
      fetch('/pengundi/analytics/pdf', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ charts: images })
      })
        .then(res => res.blob())
        .then(blob => {
          window.open(URL.createObjectURL(blob));
        })
      // .then(() => console.log("end ex"));


    });



    const DashboardState = {
      cube: [],
      totals: {},
      charts: {
        bangsa: { chart: null },
        umur: { chart: null },
        jantina: { chart: null },
        dun: { chart: null },
        negeri: { chart: null },
      }
    };


    async function loadDashboard(payload) {
      const cacheKey = 'dashboard_' + btoa(JSON.stringify(payload));
      const CACHE_TTL = 5 * 60 * 1000; // 5 minutes

      // 1️⃣ Try cache
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

      // 2️⃣ Fetch if no cache
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
      console.log('rendering (fresh)');

      // 3️⃣ Save cache
      sessionStorage.setItem(cacheKey, JSON.stringify({
        data,
        expires: Date.now() + CACHE_TTL
      }));

      applyDashboardData(data);
    }

    function applyDashboardData(data) {
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
      renderDmUmurChart(DashboardState.cube);
      // renderMalaysiaGeoChart(DashboardState.cube);
      renderNegeriChart(DashboardState.cube);
    }


    function renderNegeriChart(cube) {


      const categories = [
        ...new Set(
          cube
            .map(x => x.negeri ? x.negeri.trim() : 'UNKNOWN') // null becomes "Unknown"
        )
      ];


      if (!categories.length) return; // safety

      // 2️⃣ Define stacks
      const stacks = [
        { umno: '1', baru: '1', name: 'UMNO - First Time' },
        { umno: '1', baru: '0', name: 'UMNO - Existing' },
        { umno: '0', baru: '1', name: 'Bukan UMNO - First Time' },
        { umno: '0', baru: '0', name: 'Bukan UMNO - Existing' }
      ];

      // 3️⃣ Build series — ALWAYS return a number
      const series = stacks.map(stack => ({
        name: stack.name,
        data: categories.map(negeri => {
          const total = cube
            .filter(x =>
              (x.negeri ? x.negeri.trim() : 'UNKNOWN') === negeri &&
              x.status_umno === stack.umno &&
              x.status_baru === stack.baru
            )
            .reduce((sum, x) => sum + (Number(x.total) || 0), 0);

          return total;
        })

      }));

      // 4️⃣ Final safety check (important)
      if (
        series.some(s => !Array.isArray(s.data) || s.data.length !== categories.length)
      ) {
        console.warn('Negeri chart skipped due to invalid data', { categories, series });
        return;
      }

      renderStackedBar(
        document.querySelector('#negeriChart'),
        DashboardState.charts.negeri,
        categories,
        series,
        'Jumlah Pengundi',
        'Negeri',
        [],
        'Negeri × UMNO × First Time',
        true,
        false
      );
    }



    function renderBangsaChart(cube) {
      const categories = ['18-20', '21-29', '30-39', '40-49', '50-59', '60+']; // X-axis = Umur
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



    function renderDmUmurChart(cube) {
      // X-axis: all unique DMs
      const categories = [...new Set(cube.map(x => x.namadm))];

      // Stacks: umur groups
      const umurGroups = ['18-20', '21-29', '30-39', '40-49', '50-59', '60+'];

      // One series per umur group
      const series = umurGroups.map(umur => ({
        name: umur,
        data: categories.map(dm =>
          cube
            .filter(x =>
              x.namadm === dm &&
              x.umur_group === umur
            )
            .reduce((sum, x) => sum + x.total, 0)
        )
      }));

      renderStackedBar(
        document.querySelector('#dunChart'),
        DashboardState.charts.dun,
        categories,
        series,
        'Jumlah Pengundi',   // Y-axis
        'DM',                // X-axis
        [],
        'DM x Umur',         // Chart title
        true,               // horizontal
        false
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

    document.addEventListener('DOMContentLoaded', () => {
      // Cache elements after DOM is ready


      const modeSelect = document.getElementById('modeSelect');
      const year1Select = document.getElementById('year1');
      const year2Select = document.getElementById('year2');



      function onFilterChange() {
        loadDashboard({
          year1: year1Select.value,
          year2: year2Select.value,
          mode: modeSelect.value,
        });
      }

      // Initial load
      onFilterChange();

      // Event listeners
      modeSelect.addEventListener('change', onFilterChange);
      year1Select.addEventListener('change', onFilterChange);
      year2Select.addEventListener('change', onFilterChange);
    });


  </script>



@endpush