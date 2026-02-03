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
          <div class="chart-container" id="OverviewChart"></div>
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
            <div class="chart-container" id="jantinaChart"></div>
          </div>
        </div>


      </div>


    </div>



  </div>

  <div class="two-column-layout">
    <!-- Charts Row -->

    <div class="mb-4">
      <!-- Traffic Overview Chart -->
      <div>
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">Ahli Umno Bar (active/nnonactive) also pengunndi / not by umur_group</h5>

          </div>
          <div class="card-body">
            <div class="chart-container" id="ahliChart2"></div>
          </div>
        </div>


      </div>


    </div>

    <div class="mb-4">
      <!-- Traffic Overview Chart -->
      <div>
        <div class="card">
          <div class="card-header">
            <h5 class="card-title">Ahli Umno Doughtnut (active/nnonactive) also pengunndi / not</h5>

          </div>
          <div class="card-body">
            <div class="chart-container" id="ahliChart"></div>
          </div>
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
    const year1Select = document.getElementById('year1');
    const year2Select = document.getElementById('year2');

    let chart;

    function triggerChartLoad() {
      const mode = modeSelect.value;
      const year1 = year1Select.value;
      const year2 = year2Select.value;

      // Show / hide year2
      if (mode === 'compare') {
        year2Select.classList.remove('d-none');
      } else {
        year2Select.classList.add('d-none');
      }

      // Build payload
      let payload = { mode };

      if (mode === 'compare') {
        payload.year1 = year1;
        payload.year2 = year2;
      } else {
        payload.year = year1;
      }

      loadChart(payload);
      loadJantinaChart(payload);
      loadJantinaChart2(payload);
      loadAhliChart(payload);
      loadAhliChart2(payload);

    }

    function loadChart(filters = {}) {
      fetch('/analytics/chart/overview', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute('content')
        },
        body: JSON.stringify(filters)
      })
        .then(response => response.json())
        .then(data => {

          let umurGroups = [...new Set(data.map(d => d.umur_group))];
          let bangsaList = [...new Set(data.map(d => d.bangsa_group))];

          let series = bangsaList.map(bangsa => ({
            name: bangsa,
            data: umurGroups.map(umur => {
              let row = data.find(
                d => d.bangsa_group === bangsa && d.umur_group === umur
              );
              return row ? row.total : 0;
            })
          }));

          let options = {
            chart: {
              type: 'bar',
              stacked: true,
              height: 400
            },
            tooltip: {
              shared: true,
              intersect: false,
            },
            series: series,
            xaxis: {
              categories: umurGroups,
              title: { text: 'Umur' }
            },
            yaxis: {
              title: { text: 'Jumlah Pengundi' }
            }
          };

          if (chart) {
            chart.updateOptions(options);
          } else {
            chart = new ApexCharts(
              document.querySelector("#OverviewChart"),
              options
            );
            chart.render();
          }
        });
    }

    // 🔹 Events
    modeSelect.addEventListener('change', triggerChartLoad);
    year1Select.addEventListener('change', triggerChartLoad);
    year2Select.addEventListener('change', triggerChartLoad);


    let jantinaChart;

    function loadJantinaChart(filters = {}) {
      fetch('/analytics/chart/jantina', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(filters)
      })
        .then(res => res.json())
        .then(data => {

          const mode = filters.mode || 'single';

          // Get all labels (jantina)
          const labels = [...new Set(data.map(d => d.jantina))];

          let series = [];
          let seriesLabels = [];

          if (mode === 'single') {
            // Single year: series = totals
            series = labels.map(label => {
              const row = data.find(d => d.jantina === label);
              return row ? row.total : 0;
            });
            seriesLabels = labels;

          }

          const options = {
            chart: { type: 'donut', height: 350 },
            labels: seriesLabels,
            legend: { position: 'bottom' },
            tooltip: {
              y: {
                formatter: val => val + ' pengundi'
              }
            },
            series: mode === 'single' ? series : series[0], // initial render
            responsive: [{
              breakpoint: 480,
              options: { chart: { width: 300 }, legend: { position: 'bottom' } }
            }]
          };

          if (jantinaChart) {
            jantinaChart.updateOptions(options);

          } else {
            jantinaChart = new ApexCharts(
              document.querySelector("#jantinaChart"),
              options
            );
            jantinaChart.render();
          }





        });
    }


    let jantinaChart2;

    function loadJantinaChart2(filters = {}) {
      fetch('/analytics/chart/jantina2', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(filters)
      })
        .then(res => res.json())
        .then(data => {

          const mode = filters.mode || 'single';
          const umurGroups = [...new Set(data.map(d => d.umur_group))];
          const jantinaList = ['Perempuan', 'Lelaki'];

          let series = [];

          if (mode === 'single') {
            series = jantinaList.map(j => ({
              name: j,
              data: umurGroups.map(u => {
                const row = data.find(d => d.umur_group === u && d.jantina === j);
                return row ? row.total : 0;
              })
            }));
          }

          const options = {
            chart: { type: 'bar', stacked: true, height: 400 },
            plotOptions: { bar: { columnWidth: '50%' } },
            tooltip: { shared: true, intersect: false },
            series: series,
            xaxis: { categories: umurGroups, title: { text: 'Umur' } },
            yaxis: { title: { text: 'Jumlah Pengundi' } },
            legend: { position: 'bottom' }
          };

          if (jantinaChart2) {
            jantinaChart2.updateOptions(options);
          } else {
            jantinaChart2 = new ApexCharts(
              document.querySelector("#jantinaChart2"),
              options
            );
            jantinaChart2.render();
          }

        });
    }







    let ahliChart;

    function loadAhliChart(filters = {}) {
      fetch('/analytics/chart/ahliumno', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(filters)
      })
        .then(res => res.json())
        .then(data => {

          const mode = filters.mode || 'single';

          // Get all labels (jantina)
          const labels = data.map(d => d.status_ahli);

          let series = [];
          let seriesLabels = [];



          series = data.map(d => Number(d.total));
          seriesLabels = data.map(d => d.status_ahli);


          const options = {
            chart: { type: 'donut', height: 350 },
            labels: seriesLabels,
            legend: { position: 'bottom' },
            tooltip: {
              y: {
                formatter: val => val + ' pengundi'
              }
            },
            series: series, // initial render
            responsive: [{
              breakpoint: 480,
              options: { chart: { width: 300 }, legend: { position: 'bottom' } }
            }]
          };

          if (ahliChart) {
            ahliChart.updateOptions(options);

          } else {
            ahliChart = new ApexCharts(
              document.querySelector("#ahliChart"),
              options
            );
            ahliChart.render();
          }



        });
    }





    let ahliChart2;

    function loadAhliChart2(filters = {}) {
      fetch('/analytics/chart/ahliumno2', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(filters)
      })
        .then(res => res.json())
        .then(data => {

          const mode = filters.mode || 'single';
const umurGroups = [...new Set(data.map(d => d.umur_group))];
const statusList = [...new Set(data.map(d => d.status_ahli))];

const series = statusList.map(status => ({
  name: status,
  data: umurGroups.map(u => {
    const row = data.find(d => d.umur_group === u && d.status_ahli === status);
    return row ? Number(row.total) : 0;
  })
}));


          const options = {
            chart: { type: 'bar', stacked: true, height: 400 },
            plotOptions: { bar: { columnWidth: '50%' } },
            tooltip: { shared: true, intersect: false },
            series: series,
            xaxis: { categories: umurGroups, title: { text: 'Umur' } },
            yaxis: { title: { text: 'Jumlah Pengundi' } },
            legend: { position: 'bottom' }
          };

          if (ahliChart2) {
            ahliChart2.updateOptions(options);
          } else {
            ahliChart2 = new ApexCharts(
              document.querySelector("#ahliChart2"),
              options
            );
            ahliChart2.render();
          }

        });
    }





    triggerChartLoad();






  </script>



@endpush