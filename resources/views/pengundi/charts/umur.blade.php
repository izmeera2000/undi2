<script>

    async function renderUmurChart() {
        const mode = document.getElementById('modeSelect').value;
        const year1 = document.getElementById('year1').value;
        const year2 = document.getElementById('year2').value;

        const categories = ['18-20', '21-29', '30-39', '40-49', '50-59', '60+'];

        // Status combinations and base colors
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
            return `rgb(${Math.round(r + (255 - r) * factor)}, ${Math.round(g + (255 - g) * factor)}, ${Math.round(b + (255 - b) * factor)})`;
        };

        if (mode === 'single') {
            const cube = DashboardState.cube.filter(x => x.tarikh_undian === year1);

            const series = statusCombinations.map(combo => ({
                name: combo.label,
                data: categories.map(cat =>
                    cube
                        .filter(x => x.umur_group === cat && x.status_umno === combo.umno && x.status_baru === combo.baru)
                        .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
                ),
                color: combo.color
            }));

            await renderStackedBar(
                document.querySelector('#umurChart'),
                DashboardState.charts.umur,
                categories,
                series,
                'Jumlah Pengundi',
                'Umur',
                [],
                'Umur × Status UMNO × First Time Voter'
            );
            return;
        }

        // Compare mode
        const cube1 = DashboardState.cube.filter(x => x.tarikh_undian === year1);
        const cube2 = DashboardState.cube.filter(x => x.tarikh_undian === year2);

        const series = statusCombinations.flatMap(combo => [
            // Year1
            {
                name: `${year1} - ${combo.label}`,
                group: 'year1',
                data: categories.map(cat =>
                    cube1
                        .filter(x => x.umur_group === cat && x.status_umno === combo.umno && x.status_baru === combo.baru)
                        .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
                ),
                color: combo.color
            },
            // Year2 - lighter
            {
                name: `${year2} - ${combo.label}`,
                group: 'year2',
                data: categories.map(cat =>
                    cube2
                        .filter(x => x.umur_group === cat && x.status_umno === combo.umno && x.status_baru === combo.baru)
                        .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
                ),
                color: lightenColor(combo.color, 0.5)
            }
        ]);

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