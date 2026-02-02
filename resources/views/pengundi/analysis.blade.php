@extends('layouts.app')

@section('title', 'Dashboard')


@section('content')


  <div class="mb-4">
    <!-- Traffic Overview Chart -->
<div>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0">Overview</h5>

      <!-- Year select -->
      <div class="card-actions">
        <select id="yearSelect" class="form-select form-select-sm">
          <option value="2026" selected>2026</option>
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
  <div class="dashboard-grid dashboard-grid-4">
    <!-- Total Visitors -->
    <div class="card widget-stat">
      <div class="widget-stat-header">
        <div>
          <div class="widget-stat-value">248,532</div>
          <div class="widget-stat-label">Total Visitors</div>
        </div>
        <div class="widget-stat-icon primary">
          <i class="bi bi-people"></i>
        </div>
      </div>
      <div class="widget-stat-change positive">
        <i class="bi bi-arrow-up"></i> 24.5% vs last month
      </div>
    </div>

    <!-- Page Views -->
    <div class="card widget-stat">
      <div class="widget-stat-header">
        <div>
          <div class="widget-stat-value">1.2M</div>
          <div class="widget-stat-label">Page Views</div>
        </div>
        <div class="widget-stat-icon success">
          <i class="bi bi-eye"></i>
        </div>
      </div>
      <div class="widget-stat-change positive">
        <i class="bi bi-arrow-up"></i> 18.3% vs last month
      </div>
    </div>

    <!-- Bounce Rate -->
    <div class="card widget-stat">
      <div class="widget-stat-header">
        <div>
          <div class="widget-stat-value">32.4%</div>
          <div class="widget-stat-label">Bounce Rate</div>
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
          <div class="widget-stat-label">Avg Session</div>
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


@endsection

@push('scripts')

  <script>
    let chart;

    function loadChart(filters = {}) {

      let params = new URLSearchParams(filters).toString();

      fetch(`/analytics/chart/overview?${params}`)
        .then(response => response.json())
        .then(data => {

          // X-axis (Umur groups)
          let umurGroups = [...new Set(data.map(d => d.umur_group))];

          // Lines (Bangsa)
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

    // First load
    loadChart();
  </script>

  <script>
    let jantinaChart;

    function loadJantinaChart(filters = {}) {

      let params = new URLSearchParams(filters).toString();

      fetch(`/analytics/chart/jantina?${params}`)
        .then(response => response.json())
        .then(data => {

          // Labels (Jantina)
          let labels = data.map(d => d.jantina);

          // Series (totals)
          let series = data.map(d => d.total);

          let options = {
            chart: {
              type: 'donut',
              height: 350
            },
            series: series,
            labels: labels,
            legend: {
              position: 'bottom'
            },
            tooltip: {
              y: {
                formatter: function (val) {
                  return val + " pengundi";
                }
              }
            },
            responsive: [{
              breakpoint: 480,
              options: {
                chart: { width: 300 },
                legend: { position: 'bottom' }
              }
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

    // Initial load
    loadJantinaChart();
  </script>

<script>
let jantinaChart2;

function loadJantinaChart2(filters = {}) {
    let params = new URLSearchParams(filters).toString();

    fetch(`/analytics/chart/jantina2?${params}`)
        .then(res => res.json())
        .then(data => {

            let umurGroups = [...new Set(data.map(d => d.umur_group))];
            let jantinaList = ['Perempuan', 'Lelaki'];

            // Build series for stacked chart
            let series = jantinaList.map(j => ({
                name: j,
                data: umurGroups.map(u => {
                    let row = data.find(d => d.umur_group === u && d.jantina === j);
                    return row ? row.total : 0;
                })
            }));

            let options = {
                chart: {
                    type: 'bar',
                    stacked: true,
                    height: 400
                },
                plotOptions: {
                    bar: {
                        columnWidth: '50%',
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false
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

// Initial load
loadJantinaChart2();
</script>



@endpush