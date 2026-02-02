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
    if (config.type === 'donut' || config.type === 'pie') {
        // For donut/pie: series is just numbers, labels are separate
        const labels = data.map(d => d[config.xAxis.field]);
        config.labels = labels; // store for chart options
        return data.map(d => Number(d[config.dataA[0].value])); // series numbers
    }

    const xVals = [...new Set(data.map(d => d[config.xAxis.field]))];

    if (config.mode === 'single') {
        return config.dataA.map(s => ({
            name: s.label,
            data: xVals.map(x => {
                const row = data.find(d =>
                    d[config.xAxis.field] === x &&
                    (!s.match || d[s.match.field] === s.label)
                );
                return { x: x, y: row ? Number(row[s.value]) : 0 };
            })
        }));
    }

    if (config.mode === 'compare') {
        const years = [...new Set(data.map(d => d.tahun))].sort();
        return years.flatMap(y =>
            config.dataA.map(s => ({
                name: `${s.label} (${y})`,
                data: xVals.map(x => {
                    const row = data.find(d =>
                        d[config.xAxis.field] === x &&
                        d.tahun == y &&
                        (!s.match || d[s.match.field] === s.label)
                    );
                    return { x: x, y: row ? Number(row[s.value]) : 0 };
                })
            }))
        );
    }
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

        // Build series
        const series = buildSeries(data);

        // If donut/pie, pass labels separately
        const options = {
            chart: {
                type: config.type,
                height: config.height,
                stacked: config.type === 'bar' && config.dataA.length > 1
            },
            series: series,
            labels: (config.type === 'donut' || config.type === 'pie') ? config.labels : undefined,
            xaxis: {
                categories: xCategories,
                title: { text: config.xAxis.label ?? null }
            },
            yaxis: {
                title: { text: config.yAxis.label ?? null }
            },
            tooltip: { 
                shared: true,
                y: {
                    formatter: val => val + (config.type === 'donut' ? ' pengundi' : '')
                }
            },
            legend: { position: 'bottom' }
        };

        if (chart) {
            chart.updateOptions(options);
        } else {
            chart = new ApexCharts(document.querySelector('#' + config.id), options);
            chart.render();
        }
    });
}



    window.addEventListener('analytics:change', e => {
        loadChart(e.detail);
    });
})();
</script>
@endpush
