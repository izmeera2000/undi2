@extends('layouts.app')


@section('title', 'Culaan Analytics')

@section('breadcrumb')
    @php
        $crumbs = [
            ['label' => 'Culaan', 'url' => route('culaan.index')],
            ['label' => $culaan->name],
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
                        <input type="text" id="lokaliti" class="form-control" placeholder="Lokaliti">
                    </div>

                    <div class="col-md-3">
                        <select id="status_culaan" class="form-control">
                            <option value="">All Status</option>
                            <option value="D">Dacing (Ahli & Penyokong BN)</option>
                            <option value="A">Ahli AMANAH</option>
                            <option value="C">Condong Perikatan Nasional (PAS)</option>
                            <option value="E">Empty (Tidak Pasti)</option>
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
                    <div class="card-header">Umur</div>
                    <div class="card-body">
                        <div id="umurChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-12 mt-4">
                <div class="card">
                    <div class="card-header">Top Lokaliti</div>
                    <div class="card-body">
                        <div id="lokalitiChart"></div>
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
        function renderChart(id, labels, series, type = 'bar', title = '', colors = [], modalTitle = 'All Data', modalLabelsMap = {}) {

            if (charts[id]) charts[id].destroy();

            const isMobile = window.innerWidth <= 768;

            let options = {
                chart: {
                    type: type,
                    height: 400,
                    toolbar: { show: false },
                    events: {
                        dataPointSelection: function (event, chartContext, config) {

                            // if (!isMobile) return;

                            const w = config.w;
                            let items = [];

                            const getColor = (dataPointIndex) => {
                                return w.globals.colors?.[dataPointIndex] || '#000';
                            };

                            if (w.config.chart.type === 'pie') {

                                items = w.config.series.map((v, i) => {

                                    const code = w.config.labels[i];

                                    return {
                                        name: modalLabelsMap[code] || code || `Slice ${i + 1}`,
                                        value: v,
                                        color: getColor(i)
                                    };

                                });

                            } else {

                                w.config.series.forEach((s) => {

                                    s.data.forEach((v, index) => {

                                        const code = w.config.xaxis.categories[index];

                                        items.push({
                                            name: modalLabelsMap[code] || code || `Category ${index + 1}`,
                                            value: v,
                                            color: getColor(index)
                                        });

                                    });

                                });

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

                            document.getElementById("tooltipModalLabel").innerText = modalTitle || "All Data";
                            document.getElementById("tooltipModalBody").innerHTML = html;

                            new bootstrap.Modal(document.getElementById("tooltipModal")).show();
                        }
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
                    position: 'bottom',
                    horizontalAlign: 'center',
                    height: 60,
                    floating: false
                }
            };

            if (type === 'pie') {

                options.series = series;
                options.labels = labels;

                if (colors.length) options.colors = colors;

            } else {

                options.series = [{
                    name: 'Total',
                    data: series
                }];

                options.xaxis = { categories: labels };

                options.plotOptions = {
                    bar: {
                        distributed: true,
                        horizontal: isMobile
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
                        ['D', 'A', 'C', 'E', 'O'], // short chart labels
                        res.status_chart.series,
                        'bar',
                        'Status Culaan',
                        ['#001F7A', '#FF6600', '#009933', '#CCCCCC', '#999999'],
                        'Status Culaan Detail', // modal title
                        statusMap // modal label mapping
                    );

                    renderChart('saluranChart', res.saluran_chart.labels, res.saluran_chart.series, 'bar', 'Saluran');

                    renderChart('jantinaChart', res.jantina_chart.labels, res.jantina_chart.series, 'pie', 'Jantina', ['#FF66C4', '#36A2EB']);

                    renderChart('bangsaChart', res.bangsa_chart.labels, res.bangsa_chart.series, 'bar', 'Bangsa');

                    renderChart('umurChart', res.umur_chart.labels, res.umur_chart.series, 'bar', 'Umur');

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
                .then(res => res.blob())
                .then(blob => {
                    window.open(URL.createObjectURL(blob));
                    toastr.success("PDF Ready!");
                });

        });

    </script>
@endpush