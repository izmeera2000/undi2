<script>
  async function renderBangsaChart(payload) {
    const mode = payload.mode;
    const type1 = payload.type1;
    const series1 = payload.series1;
    const type2 = payload.type2;
    const series2 = payload.series2;

    const dataset1 = DashboardState.bangsaChart1 || [];
    const dataset2 = DashboardState.bangsaChart2 || [];

    // -----------------------------
    // Categories (Umur groups)
    // -----------------------------
    const defaultUmurGroups = ['18-20', '21-29', '30-39', '40-49', '50-59', '60+'];
    const detectedUmurGroups = [...new Set([...dataset1, ...dataset2].map(x => x.umur_group))];
    const categories = defaultUmurGroups.concat(detectedUmurGroups.filter(x => !defaultUmurGroups.includes(x)));

    // -----------------------------
    // Bangsa groups
    // -----------------------------
    const defaultBangsaGroups = ['Melayu', 'Cina', 'India', 'Lain-lain'];
    const detectedBangsaGroups = [...new Set([...dataset1, ...dataset2].map(x => x.bangsa_group))];
    const bangsaGroups = defaultBangsaGroups.concat(detectedBangsaGroups.filter(x => !defaultBangsaGroups.includes(x)));

    // -----------------------------
    // UMNO status
    // -----------------------------
    const statuses = ['1', '0']; // 1 = UMNO, 0 = Bukan UMNO

    // -----------------------------
    // Colors
    // -----------------------------
    const baseColors = {
      'UMNO - Melayu': '#991b1b', 'Bukan UMNO - Melayu': '#f87171',
      'UMNO - Cina': '#1e3a8a', 'Bukan UMNO - Cina': '#60a5fa',
      'UMNO - India': '#854d0e', 'Bukan UMNO - India': '#fbbf24',
      'UMNO - Lain-lain': '#047857', 'Bukan UMNO - Lain-lain': '#6ee7b7',
      'UNKNOWN - UNKNOWN': '#374151'
    };

    DashboardState.charts.bangsa = ensureChartRef(DashboardState.charts.bangsa);
    destroyChart(DashboardState.charts.bangsa);

    // -----------------------------
    // Build series for chart
    // -----------------------------
    const buildSeries = (dataset, typeLabel = null, seriesLabel = null, lighten = false) => {
      const prefix = '';
      return bangsaGroups.flatMap(bangsa =>
        statuses.map(status => {
          const labelStatus = status === '1' ? 'UMNO' : 'Bukan UMNO';
          const name = `${prefix}${labelStatus} - ${bangsa}`;

          const data = categories.map(umur =>
            dataset
              .filter(x =>
                (x.umur_group || '').trim() === umur &&
                (x.bangsa_group || '').trim() === bangsa &&
                String(x.status_umno) === status
              )
              .reduce((sum, x) => sum + Number(x.total || 0), 0)
          );

          const colorKey = `${labelStatus} - ${bangsa}`;
          const color = lighten ? lightenColor(baseColors[colorKey] || '#9ca3af', 0.5) : baseColors[colorKey] || '#9ca3af';
          return { name, data, color };
        })
      );
    };

    // -----------------------------
    // Render chart
    // -----------------------------
    let series;
    if (mode === 'single') {
      series = buildSeries(dataset1, type1, series1);
      await renderStackedBar(
        document.querySelector('#bangsaChart'),
        DashboardState.charts.bangsa,
        categories,
        series,
        'Jumlah Pengundi',
        'Umur',
        undefined,
        'Umur × Bangsa × Status UMNO'
      );
    } else {
      series = [
        ...buildSeries(dataset1, type1, series1),
        ...buildSeries(dataset2, type2, series2, true) // lighter colors for second dataset
      ];
      await renderStackedBar(
        document.querySelector('#bangsaChart'),
        DashboardState.charts.bangsa,
        categories,
        series,
        'Jumlah Pengundi',
        'Umur',
        undefined,
        `Perbandingan ${type1}${series1} vs ${type2}${series2} — Umur × Bangsa × Status UMNO`,
        false,
        true
      );
    }
  }
</script>