<script>
async function renderUmurChart() {

    const mode  = document.getElementById('modeSelect').value;
    const year1 = document.getElementById('year1').value;
    const year2 = document.getElementById('year2').value;

    const categories = ['18-20', '21-29', '30-39', '40-49', '50-59', '60+'];

    const statusCombinations = [
        { umno: '1', baru: '1', label: 'UMNO - First Time', color: '#991b1b' },
        { umno: '1', baru: '0', label: 'UMNO - Existing', color: '#f87171' },
        { umno: '0', baru: '1', label: 'Bukan UMNO - First Time', color: '#1e3a8a' },
        { umno: '0', baru: '0', label: 'Bukan UMNO - Existing', color: '#60a5fa' },
    ];

    DashboardState.charts.umur = DashboardState.charts.umur || { chart: null };
    if (DashboardState.charts.umur.chart) {
        DashboardState.charts.umur.chart.destroy();
        DashboardState.charts.umur.chart = null;
    }

    const lightenColor = (hex, factor = 0.5) => {
        const r = parseInt(hex.substr(1, 2), 16);
        const g = parseInt(hex.substr(3, 2), 16);
        const b = parseInt(hex.substr(5, 2), 16);
        return `rgb(${Math.round(r + (255 - r) * factor)}, 
                    ${Math.round(g + (255 - g) * factor)}, 
                    ${Math.round(b + (255 - b) * factor)})`;
    };

    const buildSeries = (dataset, yearLabel = null, lighten = false) => {

        return statusCombinations.map(combo => {

            const data = categories.map(cat =>
                dataset
                    .filter(x =>
                        x.umur_group === cat &&
                        String(x.status_umno) === combo.umno &&
                        String(x.status_baru) === combo.baru
                    )
                    .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
            );

            return {
                name: yearLabel ? `${yearLabel} - ${combo.label}` : combo.label,
                data,
                color: lighten ? lightenColor(combo.color) : combo.color,
                stack: yearLabel || 'single'
            };
        });
    };

    // 🔥 Use umurChart instead of cube
    const dataset = DashboardState.umurChart || [];

    if (mode === 'single') {

        const filtered = dataset
            .filter(x => String(x.tarikh_undian) === String(year1));

        const series = buildSeries(filtered);

        await renderStackedBar(
            document.querySelector('#umurChart'),
            DashboardState.charts.umur,
            categories,
            series,
            'Jumlah Pengundi',
            'Umur',
            [],
            `Umur × Status UMNO × First Time Voter (${year1})`,
            false,
            true
        );

        return;
    }

    // Compare mode
    const filtered1 = dataset
        .filter(x => String(x.tarikh_undian) === String(year1));

    const filtered2 = dataset
        .filter(x => String(x.tarikh_undian) === String(year2));

    const series = [
        ...buildSeries(filtered1, year1, false),
        ...buildSeries(filtered2, year2, true)
    ];

    await renderStackedBar(
        document.querySelector('#umurChart'),
        DashboardState.charts.umur,
        categories,
        series,
        'Jumlah Pengundi',
        'Umur',
        [],
        `Perbandingan ${year1} vs ${year2} — Umur × Status UMNO × First Time Voter`,
        false,
        true
    );
}
</script>
