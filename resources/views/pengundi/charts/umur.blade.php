<script>
async function renderUmurChart(payload) {
    // console.log("UMUR CHART V2 LOADED");

    const { mode, type1, series1, type2, series2 } = payload;




    // Define age categories
    const categories = ['18-20', '21-29', '30-39', '40-49', '50-59', '60+'];

    // Status combinations for stacking
    const statusCombinations = [
        { umno: '1', baru: '1', label: 'UMNO - First Time', color: '#991b1b' },
        { umno: '1', baru: '0', label: 'UMNO - Existing', color: '#f87171' },
        { umno: '0', baru: '1', label: 'Bukan UMNO - First Time', color: '#1e3a8a' },
        { umno: '0', baru: '0', label: 'Bukan UMNO - Existing', color: '#60a5fa' },
    ];

    // Initialize chart state
    DashboardState.charts.umur = DashboardState.charts.umur || { chart: null };
    if (DashboardState.charts.umur.chart) {
        DashboardState.charts.umur.chart.destroy();
        DashboardState.charts.umur.chart = null;
    }

    // Optional: lighten colors for compare mode
    const lightenColor = (hex, factor = 0.5) => {
        const r = parseInt(hex.substr(1, 2), 16);
        const g = parseInt(hex.substr(3, 2), 16);
        const b = parseInt(hex.substr(5, 2), 16);
        return `rgb(${Math.round(r + (255 - r) * factor)}, ${Math.round(g + (255 - g) * factor)}, ${Math.round(b + (255 - b) * factor)})`;
    };

const buildSeries = (dataset, labelPrefix = null, lighten = false) => {
    return statusCombinations.map(combo => {

        const data = categories.map(cat => {
            let sum = 0;

            for (const row of dataset) {
                if (
                    row.umur_group === cat &&
                    String(row.status_umno) === combo.umno &&
                    String(row.status_baru) === combo.baru
                ) {
                    sum += Number(row.total || 0);
                }
            }

            return sum;
        });

        return {
            name: combo.label, // no duplication of labelPrefix here
            data,
            color: lighten ? lightenColor(combo.color) : combo.color,
            stack: labelPrefix || 'single'
        };
    });
};

    // Load datasets
    const dataset1 = DashboardState.umurChart1 || [];
    const dataset2 = DashboardState.umurChart2 || [];

    let series = [];

    if (mode === 'single' || !dataset2.length) {
        // SINGLE MODE
        series = buildSeries(dataset1, ``, false);
        await renderStackedBar(
            document.querySelector('#umurChart'),
            DashboardState.charts.umur,
            categories,
            series,
            'Jumlah Pengundi',
            'Umur',
            [],
            `Umur × Status UMNO × First Time Voter (${type1} ${series1})`,
            false,
            true
        );
    } else if (mode === 'compare') {
        // COMPARE MODE
        series = [
            ...buildSeries(dataset1, `${type1} ${series1}`, false),
            ...buildSeries(dataset2, `${type2} ${series2}`, true)
        ];
        await renderStackedBar(
            document.querySelector('#umurChart'),
            DashboardState.charts.umur,
            categories,
            series,
            'Jumlah Pengundi',
            'Umur',
            [],
            `Perbandingan ${type1} ${series1} vs ${type2} ${series2} — Umur × Status UMNO × First Time Voter`,
            false,
            true
        );
    }
}
</script>