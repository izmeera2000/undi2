<div id="{{ $id }}"></div>

@push('scripts')
<script>
(() => {
    let chart;

    const config = {
        id: @js($id),
        type: @js($type),
        endpoint: @js($endpoint),
        height: @js($height),
        xAxis: @js($xAxis),
        yAxis: @js($yAxis),
        dataA: @js($dataA),
        dataB: @js($dataB),
    };

    function csrf() {
        return document.querySelector('meta[name="csrf-token"]').content;
    }

    function buildSeries(data) {
        const xValues = [...new Set(data.map(d => d[config.xAxis.field]))];

        return config.dataA.map(seriesCfg => ({
            name: seriesCfg.label,
            data: xValues.map(x => {
                const row = data.find(d =>
                    d[config.xAxis.field] === x &&
                    (!seriesCfg.match ||
                        d[seriesCfg.match.field] === seriesCfg.match.value)
                );
                return row ? row[seriesCfg.value] : 0;
            })
        }));
    }

    function loadChart(filters = {}) {
        fetch(config.endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf()
            },
            body: JSON.stringify(filters)
        })
        .then(r => r.json())
        .then(data => {
            const xCategories = [...new Set(data.map(d => d[config.xAxis.field]))];

            const options = {
                chart: {
                    type: config.type,
                    height: config.height,
                    stacked: config.type === 'bar' && config.dataA.length > 1
                },
                series: buildSeries(data),
                xaxis: {
                    categories: xCategories,
                    title: { text: config.xAxis.label ?? null }
                },
                yaxis: {
                    title: { text: config.yAxis.label ?? null }
                },
                tooltip: { shared: true },
                legend: { position: 'bottom' }
            };

            chart
                ? chart.updateOptions(options)
                : (chart = new ApexCharts(document.querySelector('#' + config.id), options)).render();
        });
    }

    window.addEventListener('analytics:change', e => {
        loadChart(e.detail);
    });
})();
</script>
@endpush
