@extends('layouts.app')


@section('title', 'Culaan Analytics')

@section('breadcrumb')
    @php
        $crumbs = [
            ['label' => 'Pengundi'],

            ['label' => 'Culaan', 'url' => route('culaan.index')],
            ['label' => $culaan->name ?? 'Culaan', 'url' => route('culaan.show', $culaan->id ?? 0)],
            ['label' => 'Analytics', 'url' => route('culaan.index')],

        ];
    @endphp
@endsection

@section('content')

    <div class="container-fluid">

        {{-- <h3 class="mb-4">Culaan Analytics</h3> --}}

        <!-- FILTERS -->
        <div class="card mb-4">
            <div class="card-body">

                <div class="row">


                    <div class="col-md-3">
                        <select id="dm" class="form-control">
                            <option value="">All DM</option>
                            @foreach($dmList as $dm)
                                <option value="{{ $dm->koddm }}">{{ $dm->namadm }}
                                    ({{  $dm->koddm}})</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-md-3">
                        <select id="lokaliti" class="form-control">
                            <option value="">All Lokaliti</option>
                            @foreach($lokalitiList as $lokaliti)
                                <option value="{{ $lokaliti->kod_lokaliti }}">{{ $lokaliti->nama_lokaliti }}
                                    ({{  $lokaliti->kod_lokaliti}})</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-md-3">
                        <select id="status_culaan" class="form-control">
                            <option value="">All Status</option>
                            <option value="D">BN</option>
                            <option value="A">PH</option>
                            <option value="C">Perikatan Nasional (PAS)</option>
                            <option value="E">Tidak Pasti</option>
                            <option value="O">Belum Cula</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" onclick="loadAnalytics()">
                            Filter
                        </button>
                    </div>

                    <div class="col-md-3">

                        {{-- EXPORT BUTTON --}}
                        <button id="exportPdf" class="btn btn-danger   w-100">
                            Export PDF
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- TOTAL -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <h2 id="total">0</h2>
                <p>Total Pengundi</p>
            </div>
        </div>

        <!-- CHARTS -->
        <div class="row">

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Status Culaan</div>
                    <div class="card-body">
                        <div id="statusChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Saluran</div>
                    <div class="card-body">
                        <div id="saluranChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mt-4">
                <div class="card">
                    <div class="card-header">Jantina</div>
                    <div class="card-body">
                        <div id="jantinaChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mt-4">
                <div class="card">
                    <div class="card-header">Bangsa</div>
                    <div class="card-body">
                        <div id="bangsaChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mt-4">
                <div class="card">
                    <div class="card-header">First TIme Voter</div>
                    <div class="card-body">
                        <div id="umurChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mt-4">
                <div class="card">
                    <div class="card-header">Top Lokaliti</div>
                    <div class="card-body">
                        <div class="overflow-auto" style="max-width: 100%; white-space: nowrap;">
                            <div id="lokalitiChart" style="min-width: 600px;"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>


    <div class="modal fade" id="tooltipModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="tooltipModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="tooltipModalBody"></div>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>

        let charts = {};

        const statusMap = {
            'D': 'Ahli & Penyokong BN',
            'A': 'Ahli AMANAH',
            'C': 'Condong Perikatan Nasional',
            'E': 'Tidak Pasti',
            'O': 'Belum Cula'
        };
        const DashboardState = {
            charts: {}
        };

        function renderChart(
            id,
            labels,
            series,
            type = 'bar',
            title = '',
            colors = [],
            modalTitle = 'All Data',
            modalLabelsMap = {},
            stacked = false // new param for stacked bars
        ) {
            // Destroy existing chart if present
            if (charts[id]) charts[id].destroy();

            const isMobile = window.innerWidth <= 768;

            let options = {
                chart: {
                    type: type,
                    height: 400,
                    stacked: stacked,
                    toolbar: { show: false },
                    events: {
                        dataPointSelection: function (event, chartContext, config) {
                            const w = config.w;
                            let items = [];
                            const dataPointIndex = config.dataPointIndex; // clicked category


                            const getColor = (seriesIndex, dataPointIndex) => {
                                const chartColors = w.globals.colors || w.config.colors || [];
                                const seriesLength = w.config.series.length;

                                if (w.config.chart?.type === 'pie') {
                                    // pie chart: color per slice
                                    return chartColors?.[dataPointIndex] || '#000';
                                }

                                if (seriesLength === 1) {
                                    // single series bar chart: use first color for all bars
                                    return chartColors?.[0] || '#000';
                                }

                                // multi-series / stacked bars: use seriesIndex
                                return chartColors?.[seriesIndex] || '#000';
                            };

                            if (type === 'pie') {
                                items = series.map((v, i) => {
                                    const code = labels[i];
                                    return {
                                        name: modalLabelsMap[code] || code || `Slice ${i + 1}`,
                                        value: v,
                                        color: getColor(0, i)
                                    };
                                });

                                document.getElementById("tooltipModalLabel").innerText = modalTitle || "All Data";

                            } else {

                                const isMultiSeries = w.config.series.length > 1;
                                const isStacked = w.config.chart.stacked;

                                if (isMultiSeries && isStacked) {
                                    // Bar chart: show all series for clicked category
                                    const category = w.config.xaxis.categories[dataPointIndex];

                                    w.config.series.forEach((s, seriesIndex) => {
                                        items.push({
                                            name: s.name + ' - ' + (modalLabelsMap[category] || category || `Category ${dataPointIndex + 1}`),
                                            value: s.data[dataPointIndex],
                                            color: getColor(seriesIndex)
                                        });
                                    });

                                    document.getElementById("tooltipModalLabel").innerText = modalTitle + ' ' + category || "All Data";


                                }
                                else {

                                    w.config.series.forEach((s, sIndex) => {
                                        s.data.forEach((v, index) => {
                                            const code = w.config.xaxis.categories[index];
                                            items.push({
                                                name: s.name + ' - ' + (modalLabelsMap[code] || code || `Category ${index + 1}`),
                                                value: v,
                                                color: getColor(sIndex, index)
                                            });
                                        });
                                    });
                                    document.getElementById("tooltipModalLabel").innerText = modalTitle || "All Data";


                                }



                            }

                            const html = items.map(i => `
                                                                                            <div class="tooltip-row d-flex align-items-center mb-1">
                                                                                                <span style="
                                                                                                    background:${i.color};
                                                                                                    width:12px;
                                                                                                    height:12px;
                                                                                                    display:inline-block;
                                                                                                    margin-right:6px;
                                                                                                    border-radius:3px;
                                                                                                "></span>
                                                                                                <span>${i.name}</span>
                                                                                                <strong class="ms-auto">${i.value}</strong>
                                                                                            </div>
                                                                                        `).join('');

                            document.getElementById("tooltipModalBody").innerHTML = html;
                            new bootstrap.Modal(document.getElementById("tooltipModal")).show();
                        }
                    }
                },
                colors: colors,

                plotOptions: {
                    bar: {
                        distributed: true
                    }
                },
                title: {
                    text: title,
                    align: 'center',
                    style: {
                        fontSize: '16px',
                        fontWeight: 'bold'
                    }
                },

                tooltip: {
                    enabled: false,
                    shared: true,
                    intersect: false,
                    fixed: {
                        enabled: true,
                        position: "topRight",
                        offsetX: 0,
                        offsetY: 0
                    },
                },

                legend: {
                    show: true,
                    showForSingleSeries: true,
                    position: isMobile ? 'bottom' : 'right',
                    horizontalAlign: 'center',
                    floating: false
                }
            };

            // Pie chart
            if (type === 'pie') {
                options.series = series;
                options.labels = labels;
                if (colors.length) options.colors = colors;
            } else {
                // Bar chart (single or multi-series)
                // Detect if series is an array of objects (multi-series) or flat array (single series)
                const isMultiSeries = Array.isArray(series) && series.length && series[0].hasOwnProperty('data');

                options.series = isMultiSeries
                    ? series
                    : [{ name: 'Total', data: series }];

                options.xaxis = { categories: labels };

                options.plotOptions = {
                    bar: {
                        horizontal: isMobile,
                        distributed: !stacked // only distributed for non-stacked bars
                    }
                };

                if (colors.length) options.colors = colors;
            }

            charts[id] = new ApexCharts(document.querySelector("#" + id), options);
            charts[id].render();

            DashboardState.charts[id] = {
                chart: charts[id],
                title: title
            };
        }





        function showModal(label, value) {
            const modalBody = document.querySelector('#chartModal .modal-body');
            modalBody.innerHTML = `<p><strong>${label}:</strong> ${value}</p>`;
            $('#chartModal').modal('show');
        }
        function loadAnalytics() {
            let data = {
                // search_name: $('#search_name').val(),
                dm: $('#dm').val(), // Added this
                lokaliti: $('#lokaliti').val(),
                status_culaan: $('#status_culaan').val(),
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            };

            $.ajax({
                url: "{{ route('culaan.analytics_data', $culaan->id) }}",
                type: "POST",
                data: data,
                success: function (res) {

                    $('#total').text(res.total);

                    renderChart(
                        'statusChart',
                        res.status_chart.labels,
                        res.status_chart.series,
                        'bar',
                        'Status Culaan',
                        res.status_chart.colors,
                        'Status Culaan Detail', // modal title
                        statusMap // modal label mapping
                    );

                    renderChart('saluranChart',
                        res.saluran_chart.labels,
                        res.saluran_chart.series,
                        'bar',
                        'Saluran',
                        res.status_chart.colors,
                        'Saluran',
                        {},
                        true);

                    renderChart('jantinaChart', res.jantina_chart.labels, res.jantina_chart.series, 'pie', 'Jantina', ['#FF66C4', '#36A2EB']);

                    renderChart('bangsaChart', res.bangsa_chart.labels, res.bangsa_chart.series, 'bar', 'Bangsa');

                    renderChart('umurChart', res.umur_chart.labels, res.umur_chart.series, 'bar', 'First TIme Voter', [], 'First TIme Voter', {}, true);

                    renderChart('lokalitiChart', res.lokaliti_chart.labels, res.lokaliti_chart.series, 'bar', 'Top Lokaliti');
                },
                error: function (err) {
                    console.error("Error loading analytics:", err);
                }
            });
        }

        $(document).ready(function () {
            loadAnalytics();
        });


    </script>


    <script>

        document.getElementById('exportPdf').addEventListener('click', async () => {

            toastr.info("Exporting", "Generating PDF...");

            if (!DashboardState.charts) {
                alert('Charts not ready');
                return;
            }

            const images = [];

            for (const { chart, title } of Object.values(DashboardState.charts)) {

                if (!chart) continue;

                await new Promise(r => setTimeout(r, 300));

                const { imgURI } = await chart.dataURI({ scale: 2 });

                images.push({
                    image: imgURI,
                    title: title
                });
            }

            if (!images.length) {
                alert('No charts ready for export.');
                return;
            }

            fetch("{{ route('culaan.analytics_pdf', $culaan) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ charts: images })
            })

        });


        $('#dm').on('change', function () {
            let dm = $(this).val();

            $('#lokaliti option').each(function () {
                let lokaliti = $(this).val();

                if (!lokaliti) {
                    $(this).show();
                    return;
                }

                if (dm === "" || lokaliti.startsWith(dm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            $('#lokaliti').val('');
        });

    </script>
@endpush