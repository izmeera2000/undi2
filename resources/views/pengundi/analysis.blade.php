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
       <div class="card">
        <div class="card-header">
          <h5 class="card-title">Overview</h5>

        </div>
        <div class="card-body">
          <div class="chart-container" id="OverviewChart"></div>
        </div>
      </div>


 

  </div>

  <div class="two-column-layout">


    <div class="mb-4">
      <!-- Traffic Overview Chart -->
      <div class="card">
        <div class="card-header">
          <h5 class="card-title">Jantina </h5>

        </div>
        <div class="card-body">
          <div class="chart-container" id="jantinaChart2"></div>
        </div>
      </div>




    </div>


    <div class="mb-4">
      <!-- Traffic Overview Chart -->
      <div class="card h-100">
        <div class="card-header">
          <h5 class="card-title">Jantina </h5>

        </div>
        <div class="card-body">
          <div class="chart-container" id="jantinaChart"></div>
        </div>
      </div>




    </div>



  </div>

  <div class="two-column-layout">
    <!-- Charts Row -->

    <div class="mb-4">
      <!-- Traffic Overview Chart -->
         <div class="card">
          <div class="card-header">
            <h5 class="card-title">Ahli Umno Bar (active/nnonactive) also pengunndi / not by umur_group</h5>

          </div>
          <div class="card-body">
            <div class="chart-container" id="ahliChart2"></div>
          </div>
        </div>


 

    </div>

    <div class="mb-4">
      <!-- Traffic Overview Chart -->
         <div class="card  h-100">
          <div class="card-header">
            <h5 class="card-title">Ahli Umno Doughtnut (active/nnonactive) also pengunndi / not</h5>

          </div>
          <div class="card-body">
            <div class="chart-container" id="ahliChart"></div>
          </div>
        </div>


 

    </div>


  </div>



  <div class="mb-4">
    <!-- Traffic Overview Chart -->
       <div class="card">
        <div class="card-header">
          <h5 class="card-title">radial chart by dun </h5>

        </div>
        <div class="card-body">
          <div class="chart-container" id="dundm"></div>
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


@endsection

@push('scripts')


  <script>




    document.addEventListener('DOMContentLoaded', () => {

const chartslist = {};

document.getElementById('exportPdf').addEventListener('click', async () => {
  // Map chart objects to friendly titles
  const charts = [
    { chart: overviewChart, title: 'Overview Chart' },
    { chart: jantinaChart.chart, title: 'Jantina Chart' },
    { chart: jantinaChart2.chart, title: 'Jantina by Umur' },
    { chart: ahliChart.chart, title: 'Ahli UMNO Chart' },
    { chart: ahliChart2.chart, title: 'Ahli UMNO by Umur' },
    { chart: dundmChart.chart, title: 'DUN DM Treemap' },
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




      /* ===========================
         GLOBAL HELPERS
      =========================== */

      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

      const postJSON = (url, payload) =>
        fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify(payload)
        }).then(r => r.json());

      const uniq = arr => [...new Set(arr)];

      const indexBy = (data, keys) => {
        const map = {};
        data.forEach(row => {
          const k = keys.map(k => row[k]).join('|');
          map[k] = row;
        });
        return map;
      };

      const debounce = (fn, delay = 300) => {
        let t;
        return (...args) => {
          clearTimeout(t);
          t = setTimeout(() => fn(...args), delay);
        };
      };

      /* ===========================
         SELECTORS
      =========================== */

      const modeSelect = document.getElementById('modeSelect');
      const year1Select = document.getElementById('year1');
      const year2Select = document.getElementById('year2');

      /* ===========================
         PAYLOAD BUILDER
      =========================== */

      function buildPayload() {
        const mode = modeSelect.value;
        const payload = { mode };

        year2Select.classList.toggle('d-none', mode !== 'compare');

        if (mode === 'compare') {
          payload.year1 = year1Select.value;
          payload.year2 = year2Select.value;
        } else {
          payload.year = year1Select.value;
        }

        return payload;
      }

      /* ===========================
         CHART FACTORIES
      =========================== */

      async function renderDonut(el, chartRef, labels, series, colors = []) {
        const options = {
          chart: {
            type: 'donut',
            width: '100%',
            height: '100%'
          },
          labels,
          series,
          colors,
          legend: { position: 'bottom' },
          tooltip: { y: { formatter: v => v + ' pengundi' } },
          responsive: [
            {
              breakpoint: 768,
              options: { chart: { width: '100%', height: 250 } }
            }
          ]
        };


        if (chartRef.chart) {
          chartRef.chart.updateOptions(options);
          return chartRef.chart.render(); // optional, ensures updated
        } else {
          chartRef.chart = new ApexCharts(el, options);
          await chartRef.chart.render(); // ✅ wait for render
        }
      }

      async function renderPie(el, chartRef, labels, series) {
        const options = {
          chart: {
            type: 'pie',
            width: '100%',      // full width of container
            height: 350,        // default height
          },
          labels,
          series,
          legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            offsetY: 0,
          },
          tooltip: {
            y: {
              formatter: v => v + ' pengundi'
            }
          },
          
        };

        if (chartRef.chart) {
          chartRef.chart.updateOptions(options);
          return chartRef.chart.render(); // update chart if already exists
        } else {
          chartRef.chart = new ApexCharts(el, options);
          await chartRef.chart.render();   // wait for initial render
        }
      }




      async function renderStackedBar(el, chartRef, categories, series, yTitle = '', colors = []) {
        const options = {
          chart: { type: 'bar', stacked: true, height: 400 },
          plotOptions: { bar: { columnWidth: '50%' } },
          tooltip: { shared: true, intersect: false },
          series,
          colors,
          xaxis: { categories },
          yaxis: { title: { text: yTitle } },
          legend: { position: 'bottom' }
        };

        if (chartRef.chart) {
          chartRef.chart.updateOptions(options);
          return chartRef.chart.render();
        } else {
          chartRef.chart = new ApexCharts(el, options);
          await chartRef.chart.render();
        }
      }

      async function renderTreemap(el, chartRef, series) {
        const colors = ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#26a69a', '#ff7043'];
        const options = {
          chart: { type: 'treemap', height: 450, toolbar: { show: true } },
          series,
          legend: { show: false },
          dataLabels: { enabled: true, style: { fontSize: '12px', colors: ['#fff'] }, offsetY: -4 },
          plotOptions: { treemap: { distributed: true, enableShades: true, shadeIntensity: 0.5, reverseNegativeShade: true } },
          tooltip: { y: { formatter: val => val + ' pengundi' }, x: { formatter: val => val } },
          colors
        };

        if (chartRef.chart) {
          chartRef.chart.updateOptions(options);
          return chartRef.chart.render();
        } else {
          chartRef.chart = new ApexCharts(el, options);
          await chartRef.chart.render();
        }
      }

      /* ===========================
         OVERVIEW CHART
      =========================== */

      let overviewChart;

      async function loadOverview(payload) {
        const data = await postJSON('/analytics/chart/overview', payload);

        const umurGroups = uniq(data.map(d => d.umur_group));
        const bangsaList = uniq(data.map(d => d.bangsa_group));
        const index = indexBy(data, ['bangsa_group', 'umur_group']);

        const series = bangsaList.map(b => ({
          name: b,
          data: umurGroups.map(u => index[`${b}|${u}`]?.total ?? 0)
        }));

        const options = {
          chart: { type: 'bar', stacked: true, height: 400 },
          tooltip: { shared: true, intersect: false },
          series,
          xaxis: { categories: umurGroups, title: { text: 'Umur' } },
          yaxis: { title: { text: 'Jumlah Pengundi' } }
        };

        if (overviewChart) overviewChart.updateOptions(options);
        else {
          overviewChart = new ApexCharts(
            document.querySelector('#OverviewChart'),
            options
          );
          overviewChart.render();
        }
      }

      /* ===========================
         JANTINA DONUT
      =========================== */

      const jantinaChart = {};

      async function loadJantina(payload) {
        const data = await postJSON('/analytics/chart/jantina', payload);
        const colors = ['#FF1493', '#1E3A8A']; // Perempuan = green, Lelaki = red

        await renderDonut(
          document.querySelector('#jantinaChart'),
          jantinaChart,
          data.map(d => d.jantina),
          data.map(d => Number(d.total)),
          colors
        );
      }

      /* ===========================
         JANTINA BY UMUR
      =========================== */
      const jantinaChart2 = {};

      async function loadJantinaByUmur(payload) {
        const data = await postJSON('/analytics/chart/jantina2', payload);

        const umurGroups = uniq(data.map(d => d.umur_group));
        const jantinaList = uniq(data.map(d => d.jantina));
        const index = indexBy(data, ['umur_group', 'jantina']);

        const series = jantinaList.map(j => ({
          name: j,
          data: umurGroups.map(u => index[`${u}|${j}`]?.total ?? 0)
        }));
        const colors = ['#FF1493', '#1E3A8A']; // Perempuan = green, Lelaki = red
        await renderStackedBar(
          document.querySelector('#jantinaChart2'),
          jantinaChart2,
          umurGroups,
          series,
          'Jumlah Pengundi',
          colors
        );
      }


      /* ===========================
         AHLI UMNO DONUT
      =========================== */

      const ahliChart = {};

      async function loadAhli(payload) {
        const data = await postJSON('/analytics/chart/ahliumno', payload);

        await renderPie(
          document.querySelector('#ahliChart'),
          ahliChart,
          data.map(d => d.status_ahli),
          data.map(d => Number(d.total))
        );
      }

      /* ===========================
         AHLI UMNO BY UMUR
      =========================== */

      const ahliChart2 = {};

      async function loadAhliByUmur(payload) {
        const data = await postJSON('/analytics/chart/ahliumno2', payload);

        const umurGroups = uniq(data.map(d => d.umur_group));
        const statusList = uniq(data.map(d => d.status_ahli));
        const index = indexBy(data, ['umur_group', 'status_ahli']);

        const series = statusList.map(s => ({
          name: s,
          data: umurGroups.map(u => index[`${u}|${s}`]?.total ?? 0)
        }));


        await renderStackedBar(
          document.querySelector('#ahliChart2'),
          ahliChart2,
          umurGroups,
          series,
          'Jumlah Pengundi'
        );
      }


      /* ===========================
   dundm
  =========================== */
      const dundmChart = {};

      async function loadDunDm(payload) {
        const data = await postJSON('/analytics/chart/dundm', payload);

        // Group data by DUN
        const dunGroups = {};
        data.forEach(item => {
          if (!dunGroups[item.namadun]) dunGroups[item.namadun] = [];
          dunGroups[item.namadun].push({ x: item.namadm, y: Number(item.total) });
        });

        // Transform into ApexCharts series format
        const series = Object.keys(dunGroups).map(dun => ({
          name: dun,
          data: dunGroups[dun]
        }));

        await renderTreemap(
          document.querySelector('#dundm'),
          dundmChart,
          series
        );
      }

      /* ===========================
    dundm BY UMUR
  =========================== */

      const dundmChart2 = {};

      async function loadDundmByUmur(payload) {
        const data = await postJSON('/analytics/chart/dundm2', payload);

        // Extract DUNs
        const dunList = [...new Set(data.map(d => d.namadun))];

        // Index data by DUN → umur_group
        const index = {};
        data.forEach(d => {
          index[`${d.namadun}|${d.umur_group}`] = d.total;
        });

        // Age groups
        const umurGroups = [...new Set(data.map(d => d.umur_group))].sort();

        // Build series per DUN
        const series = dunList.map(dun => ({
          name: dun,
          data: umurGroups.map(umur => index[`${dun}|${umur}`] || 0)
        }));

        // Optional colors for DUNs (or leave default)
        const colors = ['#1E3A8A', '#FF1493', '#F59E0B', '#10B981', '#8B5CF6'];

        await renderStackedBar(
          document.querySelector('#dundmChart2'),
          dundmChart2,
          umurGroups,
          series,
          'Jumlah Pengundi',
          colors
        );
      }



      const dundmChartGrouped = {};

      async function loadDundmGrouped(payload) {
        const data = await postJSON('/analytics/chart/dundm2spec', payload);

        const dunList = uniq(data.map(d => d.namadun));
        const dmList = uniq(data.map(d => d.namadm));
        const umurGroups = ['18-20', '21-29', '30-39', '40-49', '50-59', '60+'];

        const index = {};
        data.forEach(d => {
          index[`${d.namadun}|${d.namadm}|${d.umur_group}`] = d.total;
        });

        const series = umurGroups.map(umur => ({
          name: umur,
          data: dmList.map(dm => {
            const dun = data.find(d => d.namadm === dm)?.namadun;
            return index[`${dun}|${dm}|${umur}`] || 0;
          })
        }));

        const options = {
          chart: { type: 'bar', stacked: true, height: 950 },
          plotOptions: { bar: { horizontal: true, columnWidth: '50%' } },
          xaxis: { categories: dmList, title: { text: 'Umur' } },
          legend: { position: 'bottom' },
          tooltip: { shared: true, intersect: false },
          series
        };

        if (dundmChartGrouped.chart) {
          dundmChartGrouped.chart.updateOptions(options);
        } else {
          dundmChartGrouped.chart = new ApexCharts(
            document.querySelector('#dundmChartGrouped'),
            options
          );
          dundmChartGrouped.chart.render();
        }
      }




      document.getElementById('loadDunChart').addEventListener('click', () => {
        const selectedDun = document.getElementById('dunSelect').value;

        const payload = {
          dun: selectedDun || ""
        };

        loadDundmGrouped(payload);
      });





      /* ===========================
         MASTER TRIGGER
      =========================== */

      const reloadAll = debounce(async () => {
        const payload = buildPayload();

        await Promise.all([
          loadOverview(payload),
          loadJantina(payload),
          loadJantinaByUmur(payload),
          loadAhli(payload),
          loadAhliByUmur(payload),
          loadDunDm(payload),
          loadDundmGrouped(payload)
        ]);

        console.log('All charts fully rendered ✅');
      }, 300);


      modeSelect.addEventListener('change', reloadAll);
      year1Select.addEventListener('change', reloadAll);
      year2Select.addEventListener('change', reloadAll);



      reloadAll();

    });





  </script>


  <script>
    // Pass PHP $years to JS
    const availableYears = @json($years);

    console.log('Available years:', availableYears);
  </script>

@endpush