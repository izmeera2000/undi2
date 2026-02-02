@extends('layouts.app')

@section('title', 'Dashboard')


@section('content')
  <!-- Welcome & Stats Row -->
  <div class="row g-4 mb-4">
    <!-- Welcome Card -->
    <div class="col-12 col-xxl-5">
      <div class="card welcome-card h-100">
        <div class="card-body d-flex align-items-center">
          <div class="welcome-content">
            <p class="welcome-greeting">Good day,</p>
            <h3 class="welcome-name">David Dev!</h3>
            <div class="welcome-date">
              <i class="bi bi-calendar3"></i>
              <span id="currentDate">Jan 23, 2026</span>
              <i class="bi bi-clock ms-3"></i>
              <span id="currentTime">15:43:08</span>
            </div>
          </div>
          <div class="welcome-illustration">
            <img src="assets/img/illustrations/welcome.svg" alt="Welcome" onerror="this.style.display='none'">
          </div>
        </div>
      </div>
    </div>

    <x-modern_stat_card col="col-12 col-md-4 col-xxl" title="Orders" icon="bi bi-bag-check" number="9,754" change=""
      changeClass="" subtitle="" />


    <x-modern_stat_card col="col-12 col-md-4 col-xxl" title="Revenue" icon="bi bi-currency-dollar" iconClass="success" number="$75.21k" change="5.23%"
      changeClass="positive" subtitle="Since last month" />

    <x-modern_stat_card  col="col-12 col-md-4 col-xxl" title="Growth" icon="bi bi-graph-up-arrow" iconClass="info" number="+25.08%" change="4.87%"
      changeClass="positive" subtitle="Since last month" />




  </div>

  <!-- Performance Row -->
  <div class="row g-4 mb-4">
    <!-- Store Performance Analytics -->
    <div class="col-12 col-xl-5">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">Store Performance Analytics</h5>
          <button class="btn-icon" title="Refresh">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
        </div>
        <div class="card-body">
          <div id="performanceChart" class="chart-container"></div>
        </div>
      </div>
    </div>

    <!-- Weekly Performance Insights -->
    <div class="col-12 col-xl-7">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">Weekly Performance Insights</h5>
          <div class="dropdown">
            <button class="btn-icon" data-bs-toggle="dropdown">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#">View Report</a></li>
              <li><a class="dropdown-item" href="#">Download CSV</a></li>
            </ul>
          </div>
        </div>
        <div class="card-body">
          <div id="weeklyInsightsChart" class="chart-container"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sales & Products Row -->
  <div class="row g-4 mb-4">
    <!-- Sales Report -->
    <div class="col-12 col-xl-7">
      <div class="card h-100">
        <div class="card-header">
          <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
              <h5 class="card-title mb-1">Sales Report</h5>
              <p class="text-muted small mb-0">25,822 Orders</p>
            </div>
            <ul class="nav nav-pills nav-pills-sm" id="salesTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="today-tab" data-bs-toggle="pill" data-bs-target="#today" type="button"
                  role="tab">Today</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="monthly-tab" data-bs-toggle="pill" data-bs-target="#monthly"
                  type="button" role="tab">Monthly</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="annual-tab" data-bs-toggle="pill" data-bs-target="#annual" type="button"
                  role="tab">Annual</button>
              </li>
            </ul>
          </div>
        </div>
        <div class="card-body">
          <!-- Sales Metrics -->
          <div class="row g-4 mb-4">
            <div class="col-4">
              <div class="sales-metric">
                <div class="sales-metric-icon primary">
                  <i class="bi bi-wallet2"></i>
                </div>
                <div>
                  <div class="sales-metric-label">Revenue</div>
                  <div class="sales-metric-value">$78,224.68</div>
                </div>
              </div>
            </div>
            <div class="col-4">
              <div class="sales-metric">
                <div class="sales-metric-icon success">
                  <i class="bi bi-box-seam"></i>
                </div>
                <div>
                  <div class="sales-metric-label">Orders</div>
                  <div class="sales-metric-value">8,541</div>
                </div>
              </div>
            </div>
            <div class="col-4">
              <div class="sales-metric">
                <div class="sales-metric-icon info">
                  <i class="bi bi-graph-up"></i>
                </div>
                <div>
                  <div class="sales-metric-label">Growth Rate</div>
                  <div class="sales-metric-value">25.30%</div>
                </div>
              </div>
            </div>
          </div>
          <!-- Sales Chart -->
          <div class="tab-content" id="salesTabsContent">
            <div class="tab-pane fade show active" id="monthly" role="tabpanel">
              <div id="salesChart" class="chart-container"></div>
            </div>
            <div class="tab-pane fade" id="today" role="tabpanel">
              <div id="salesTodayChart" class="chart-container"></div>
            </div>
            <div class="tab-pane fade" id="annual" role="tabpanel">
              <div id="salesAnnualChart" class="chart-container"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Top Selling Products -->
    <div class="col-12 col-xl-5">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">Top Selling Products</h5>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-download"></i> Export
            </button>
            <button class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-upload"></i> Import
            </button>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover products-table mb-0">
              <thead>
                <tr>
                  <th>Product</th>
                  <th class="text-end">Price</th>
                  <th class="text-end">Qty</th>
                  <th class="text-end">Amount</th>
                  <th class="text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <div class="product-info">
                      <img src="assets/img/products/product-1.webp" alt="Product" class="product-img">
                      <div>
                        <div class="product-name">Modern Fabric Sofa Set</div>
                        <div class="product-brand">By: Homeluxe</div>
                      </div>
                    </div>
                  </td>
                  <td class="text-end">$499.00</td>
                  <td class="text-end">34</td>
                  <td class="text-end fw-semibold">$16,966.00</td>
                  <td class="text-center"><span class="badge badge-soft-warning">Low Stock</span></td>
                </tr>
                <tr>
                  <td>
                    <div class="product-info">
                      <img src="assets/img/products/product-2.webp" alt="Product" class="product-img">
                      <div>
                        <div class="product-name">L-Shaped Sectional Sofa</div>
                        <div class="product-brand">By: ComfortHub</div>
                      </div>
                    </div>
                  </td>
                  <td class="text-end">$899.00</td>
                  <td class="text-end">21</td>
                  <td class="text-end fw-semibold">$18,879.00</td>
                  <td class="text-center"><span class="badge badge-soft-success">In Stock</span></td>
                </tr>
                <tr>
                  <td>
                    <div class="product-info">
                      <img src="assets/img/products/product-3.webp" alt="Product" class="product-img">
                      <div>
                        <div class="product-name">Velvet Recliner Chair</div>
                        <div class="product-brand">By: SoftEase</div>
                      </div>
                    </div>
                  </td>
                  <td class="text-end">$379.00</td>
                  <td class="text-end">47</td>
                  <td class="text-end fw-semibold">$17,813.00</td>
                  <td class="text-center"><span class="badge badge-soft-success">In Stock</span></td>
                </tr>
                <tr>
                  <td>
                    <div class="product-info">
                      <img src="assets/img/products/product-5.webp" alt="Product" class="product-img">
                      <div>
                        <div class="product-name">Minimalist TV Stand</div>
                        <div class="product-brand">By: FurniPro</div>
                      </div>
                    </div>
                  </td>
                  <td class="text-end">$315.00</td>
                  <td class="text-end">64</td>
                  <td class="text-end fw-semibold">$20,160.00</td>
                  <td class="text-center"><span class="badge badge-soft-success">In Stock</span></td>
                </tr>
                <tr>
                  <td>
                    <div class="product-info">
                      <img src="assets/img/products/product-6.webp" alt="Product" class="product-img">
                      <div>
                        <div class="product-name">Leather Lounge Chair</div>
                        <div class="product-brand">By: UrbanStyle</div>
                      </div>
                    </div>
                  </td>
                  <td class="text-end">$425.00</td>
                  <td class="text-end">39</td>
                  <td class="text-end fw-semibold">$16,575.00</td>
                  <td class="text-center"><span class="badge badge-soft-warning">Low Stock</span></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="p-3 text-center border-top">
            <small class="text-muted">Showing 1 to 6 of 12 products</small>
            <nav class="d-inline-flex ms-3">
              <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled"><a class="page-link" href="#"><i class="bi bi-chevron-left"></i></a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#"><i class="bi bi-chevron-right"></i></a></li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Orders & Activity Row -->
  <div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-12 col-lg-5">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <div>
            <h5 class="card-title mb-1">Recent Orders</h5>
            <p class="text-muted small mb-0">186.25k Transactions</p>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-download"></i> Export
            </button>
            <button class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-upload"></i> Import
            </button>
          </div>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover orders-table mb-0">
              <thead>
                <tr>
                  <th>#ID <i class="bi bi-arrow-down-up"></i></th>
                  <th>Customer <i class="bi bi-arrow-down-up"></i></th>
                  <th>Date <i class="bi bi-arrow-down-up"></i></th>
                  <th>Amount <i class="bi bi-arrow-down-up"></i></th>
                  <th>Payment <i class="bi bi-arrow-down-up"></i></th>
                  <th>Status <i class="bi bi-arrow-down-up"></i></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><a href="#">#ORD-1023</a></td>
                  <td>John Carter</td>
                  <td>12 Nov 2025</td>
                  <td>$249.00</td>
                  <td>Credit Card</td>
                  <td><span class="badge badge-soft-success">Completed</span></td>
                </tr>
                <tr>
                  <td><a href="#">#ORD-1022</a></td>
                  <td>Sarah Wilson</td>
                  <td>11 Nov 2025</td>
                  <td>$189.00</td>
                  <td>PayPal</td>
                  <td><span class="badge badge-soft-warning">Pending</span></td>
                </tr>
                <tr>
                  <td><a href="#">#ORD-1021</a></td>
                  <td>Mike Johnson</td>
                  <td>10 Nov 2025</td>
                  <td>$549.00</td>
                  <td>Bank Transfer</td>
                  <td><span class="badge badge-soft-success">Completed</span></td>
                </tr>
                <tr>
                  <td><a href="#">#ORD-1020</a></td>
                  <td>Emily Davis</td>
                  <td>09 Nov 2025</td>
                  <td>$129.00</td>
                  <td>Credit Card</td>
                  <td><span class="badge badge-soft-danger">Cancelled</span></td>
                </tr>
                <tr>
                  <td><a href="#">#ORD-1019</a></td>
                  <td>Robert Brown</td>
                  <td>08 Nov 2025</td>
                  <td>$399.00</td>
                  <td>Credit Card</td>
                  <td><span class="badge badge-soft-info">Processing</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Revenue by Location -->
    <div class="col-12 col-lg-4">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">Revenue By Locations</h5>
          <div class="dropdown">
            <button class="btn-icon" data-bs-toggle="dropdown">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#">View Details</a></li>
              <li><a class="dropdown-item" href="#">Download Report</a></li>
            </ul>
          </div>
        </div>
        <div class="card-body">
          <div id="locationChart" class="chart-container mb-3"></div>
          <div class="location-stats">
            <div class="location-stat-item">
              <div class="location-stat-bar">
                <div class="location-stat-progress" style="width: 45%; background: var(--accent-color);"></div>
              </div>
              <div class="location-stat-info">
                <span class="location-stat-name">United States</span>
                <span class="location-stat-value">$24,500</span>
              </div>
            </div>
            <div class="location-stat-item">
              <div class="location-stat-bar">
                <div class="location-stat-progress" style="width: 32%; background: var(--success-color);"></div>
              </div>
              <div class="location-stat-info">
                <span class="location-stat-name">United Kingdom</span>
                <span class="location-stat-value">$17,200</span>
              </div>
            </div>
            <div class="location-stat-item">
              <div class="location-stat-bar">
                <div class="location-stat-progress" style="width: 28%; background: var(--warning-color);"></div>
              </div>
              <div class="location-stat-info">
                <span class="location-stat-name">Germany</span>
                <span class="location-stat-value">$15,100</span>
              </div>
            </div>
            <div class="location-stat-item">
              <div class="location-stat-bar">
                <div class="location-stat-progress" style="width: 18%; background: var(--info-color);"></div>
              </div>
              <div class="location-stat-info">
                <span class="location-stat-name">Canada</span>
                <span class="location-stat-value">$9,800</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-12 col-lg-3">
      <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="card-title mb-0">Recent Activity</h5>
          <div class="dropdown">
            <button class="btn-icon" data-bs-toggle="dropdown">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#">View All</a></li>
              <li><a class="dropdown-item" href="#">Clear All</a></li>
            </ul>
          </div>
        </div>
        <div class="card-body widget-activity">
          <div class="activity-item">
            <div class="activity-icon primary">
              <i class="bi bi-arrow-repeat"></i>
            </div>
            <div class="activity-content">
              <div class="activity-title">New Orders Synced from Storefront</div>
              <div class="activity-text">1,250 new customer orders were successfully imported from the online store.
              </div>
              <div class="activity-time">2 hours ago</div>
            </div>
          </div>
          <div class="activity-item">
            <div class="activity-icon success">
              <i class="bi bi-check-circle"></i>
            </div>
            <div class="activity-content">
              <div class="activity-title">Payment Received</div>
              <div class="activity-text">Invoice #INV-2024-089 has been paid by John Carter.</div>
              <div class="activity-time">4 hours ago</div>
            </div>
          </div>
          <div class="activity-item">
            <div class="activity-icon warning">
              <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="activity-content">
              <div class="activity-title">Low Inventory Alert</div>
              <div class="activity-text">5 products are running low on stock. Review inventory levels.</div>
              <div class="activity-time">6 hours ago</div>
            </div>
          </div>
          <div class="activity-item">
            <div class="activity-icon info">
              <i class="bi bi-person-plus"></i>
            </div>
            <div class="activity-content">
              <div class="activity-title">New Customer Registered</div>
              <div class="activity-text">Emma Wilson created a new account.</div>
              <div class="activity-time">8 hours ago</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>


@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Update date and time
      function updateDateTime() {
        const now = new Date();
        const dateOptions = {
          month: 'short',
          day: 'numeric',
          year: 'numeric'
        };
        const timeOptions = {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit',
          hour12: false
        };
        document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', dateOptions);
        document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', timeOptions);
      }
      updateDateTime();
      setInterval(updateDateTime, 1000);
      // Get theme colors
      const accentColor = getComputedStyle(document.documentElement).getPropertyValue('--accent-color').trim();
      const successColor = getComputedStyle(document.documentElement).getPropertyValue('--success-color').trim();
      const warningColor = getComputedStyle(document.documentElement).getPropertyValue('--warning-color').trim();
      const infoColor = getComputedStyle(document.documentElement).getPropertyValue('--info-color').trim();
      const dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--danger-color').trim();
      const mutedColor = getComputedStyle(document.documentElement).getPropertyValue('--muted-color').trim();
      const borderColor = getComputedStyle(document.documentElement).getPropertyValue('--border-color').trim();
      // Store Performance Analytics - Donut Chart
      const performanceOptions = {
        series: [44, 55, 41, 17],
        chart: {
          type: 'donut',
          height: 300,
          fontFamily: 'inherit'
        },
        labels: ['Electronics', 'Furniture', 'Clothing', 'Accessories'],
        colors: [accentColor, successColor, warningColor, infoColor],
        plotOptions: {
          pie: {
            donut: {
              size: '70%',
              labels: {
                show: true,
                name: {
                  fontSize: '14px',
                  fontWeight: 500
                },
                value: {
                  fontSize: '24px',
                  fontWeight: 600,
                  formatter: function (val) {
                    return val + '%';
                  }
                },
                total: {
                  show: true,
                  label: 'Total',
                  fontSize: '14px',
                  fontWeight: 500,
                  formatter: function (w) {
                    return '140';
                  }
                }
              }
            }
          }
        },
        dataLabels: {
          enabled: false
        },
        legend: {
          position: 'bottom',
          fontSize: '13px',
          markers: {
            width: 10,
            height: 10,
            radius: 4
          }
        },
        stroke: {
          width: 2
        }
      };
      new ApexCharts(document.querySelector('#performanceChart'), performanceOptions).render();
      // Weekly Performance Insights - Horizontal Bar Chart
      const weeklyOptions = {
        series: [{
          name: 'Performance',
          data: [65, 78, 52, 89, 73, 95, 82]
        }],
        chart: {
          type: 'bar',
          height: 300,
          fontFamily: 'inherit',
          toolbar: {
            show: false
          }
        },
        plotOptions: {
          bar: {
            horizontal: true,
            borderRadius: 4,
            barHeight: '60%',
            distributed: true
          }
        },
        colors: [accentColor, accentColor, accentColor, accentColor, accentColor, successColor, accentColor],
        dataLabels: {
          enabled: true,
          formatter: function (val) {
            return val;
          },
          style: {
            fontSize: '12px',
            fontWeight: 500
          }
        },
        xaxis: {
          categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          labels: {
            style: {
              colors: mutedColor,
              fontSize: '12px'
            }
          },
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          }
        },
        yaxis: {
          labels: {
            style: {
              colors: mutedColor,
              fontSize: '12px'
            }
          }
        },
        grid: {
          borderColor: borderColor,
          strokeDashArray: 4,
          xaxis: {
            lines: {
              show: true
            }
          },
          yaxis: {
            lines: {
              show: false
            }
          }
        },
        legend: {
          show: false
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + ' orders';
            }
          }
        }
      };
      new ApexCharts(document.querySelector('#weeklyInsightsChart'), weeklyOptions).render();
      // Sales Report - Area Chart
      const salesOptions = {
        series: [{
          name: 'Revenue',
          data: [28, 40, 36, 52, 38, 60, 55, 75, 65, 85, 80, 95, 90, 100, 88, 110, 105, 130, 115, 140, 135, 155, 145, 165, 160]
        }, {
          name: 'Orders',
          data: [15, 25, 20, 35, 25, 40, 35, 50, 40, 55, 50, 65, 55, 70, 60, 75, 70, 90, 80, 95, 85, 105, 95, 115, 110]
        }],
        chart: {
          type: 'area',
          height: 280,
          fontFamily: 'inherit',
          toolbar: {
            show: false
          },
          zoom: {
            enabled: false
          }
        },
        colors: [accentColor, successColor],
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth',
          width: 2
        },
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.1,
            stops: [0, 90, 100]
          }
        },
        xaxis: {
          categories: Array.from({
            length: 25
          }, (_, i) => i + 1),
          labels: {
            style: {
              colors: mutedColor,
              fontSize: '11px'
            }
          },
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          }
        },
        yaxis: {
          labels: {
            style: {
              colors: mutedColor,
              fontSize: '12px'
            },
            formatter: function (val) {
              return '$' + val + 'k';
            }
          }
        },
        grid: {
          borderColor: borderColor,
          strokeDashArray: 4,
          xaxis: {
            lines: {
              show: false
            }
          }
        },
        legend: {
          position: 'top',
          horizontalAlign: 'right',
          fontSize: '13px',
          markers: {
            width: 10,
            height: 10,
            radius: 4
          }
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return '$' + val + 'k';
            }
          }
        }
      };
      const salesChart = new ApexCharts(document.querySelector('#salesChart'), salesOptions);
      salesChart.render();
      // Location Chart - Simple Bar
      const locationOptions = {
        series: [{
          data: [45, 32, 28, 18]
        }],
        chart: {
          type: 'bar',
          height: 150,
          fontFamily: 'inherit',
          toolbar: {
            show: false
          },
          sparkline: {
            enabled: true
          }
        },
        plotOptions: {
          bar: {
            horizontal: false,
            borderRadius: 4,
            columnWidth: '60%',
            distributed: true
          }
        },
        colors: [accentColor, successColor, warningColor, infoColor],
        dataLabels: {
          enabled: false
        },
        legend: {
          show: false
        },
        xaxis: {
          categories: ['US', 'UK', 'DE', 'CA']
        }
      };
      new ApexCharts(document.querySelector('#locationChart'), locationOptions).render();
      // Theme change handler
      document.addEventListener('themeChanged', function () {
        const newBorderColor = getComputedStyle(document.documentElement).getPropertyValue('--border-color').trim();
        const newMutedColor = getComputedStyle(document.documentElement).getPropertyValue('--muted-color').trim();
        // Charts will update on next render
      });
    });
  </script>
@endpush