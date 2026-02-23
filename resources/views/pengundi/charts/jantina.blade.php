<script>
async function renderJantinaChart(payload) {
    // console.log('--- renderJantinaChart START ---');

    const { mode, type1, series1, type2, series2 } = payload;
    const categories = ['Lelaki', 'Perempuan'];

    const statusMap = { '1': 'UMNO', '0': 'Bukan UMNO' };
    const colors = {
        'UMNO-Lelaki': '#9f1239',         
        'UMNO-Perempuan': '#fb7185',      
        'Bukan UMNO-Lelaki': '#1e40af',   
        'Bukan UMNO-Perempuan': '#93c5fd' 
    };

    const lightenColor = (hex, factor = 0.5) => {
        const r = parseInt(hex.substr(1, 2), 16);
        const g = parseInt(hex.substr(3, 2), 16);
        const b = parseInt(hex.substr(5, 2), 16);
        return `rgb(${Math.round(r + (255 - r) * factor)}, ${Math.round(g + (255 - g) * factor)}, ${Math.round(b + (255 - b) * factor)})`;
    };

    const dataset1 = DashboardState.jantinaChart1 || [];
    const dataset2 = DashboardState.jantinaChart2 || [];

    DashboardState.charts.jantina = DashboardState.charts.jantina || { chart: null };
    if (DashboardState.charts.jantina.chart) {
        DashboardState.charts.jantina.chart.destroy();
        DashboardState.charts.jantina.chart = null;
    }

    const buildSeries = (dataset, labelPrefix = null, lighten = false) => {
        const combinations = [
            { status: 'UMNO', gender: 'Lelaki' },
            { status: 'UMNO', gender: 'Perempuan' },
            { status: 'Bukan UMNO', gender: 'Lelaki' },
            { status: 'Bukan UMNO', gender: 'Perempuan' }
        ];

        return combinations.map(combo => {
            const value = dataset
                .filter(x => x.jantina === combo.gender && statusMap[String(x.status_umno)] === combo.status)
                .reduce((acc, x) => acc + Number(x.total || 0), 0);

            // Only put value in correct category index, others are null
            const data = categories.map(cat => cat === combo.gender ? value : null);

            return {
                name: labelPrefix ? `${labelPrefix} - ${combo.status} - ${combo.gender}` : `${combo.status} - ${combo.gender}`,
                data,
                color: lighten ? lightenColor(colors[`${combo.status}-${combo.gender}`]) : colors[`${combo.status}-${combo.gender}`],
                stack: labelPrefix || 'single'
            };
        });
    };

    let series = [];

    if (mode === 'single' || !dataset2.length) {
        series = buildSeries(dataset1, ``, false);
        await renderStackedBar(
            document.querySelector('#jantinaChart'),
            DashboardState.charts.jantina,
            categories,
            series,
            'Jumlah Pengundi',
            'Jantina',
            [],
            `Jantina × Status UMNO (${type1} ${series1})`,
            false,
            true
        );
    } else if (mode === 'compare') {
        series = [
            ...buildSeries(dataset1, `${type1} ${series1}`, false),
            ...buildSeries(dataset2, `${type2} ${series2}`, true)
        ];
        await renderStackedBar(
            document.querySelector('#jantinaChart'),
            DashboardState.charts.jantina,
            categories,
            series,
            'Jumlah Pengundi',
            'Jantina',
            [],
            `Perbandingan ${type1} ${series1} vs ${type2} ${series2} — Jantina × Status UMNO`,
            false,
            true
        );
    }

    // console.log('--- renderJantinaChart END ---');
}
</script>