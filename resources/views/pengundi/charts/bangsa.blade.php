<script>
  async function renderBangsaChart() {
    const mode = document.getElementById('modeSelect').value;
    const year1 = document.getElementById('year1').value;
    const year2 = document.getElementById('year2').value;

    const dataSource = DashboardState.bangsaChart || [];

    console.log('Bangsa chart data:', dataSource);

    // -------------------------
    // Dynamic Categories
    // -------------------------

    const defaultCategories = ['18-20', '21-29', '30-39', '40-49', '50-59', '60+'];
    const hasUnknownUmur = dataSource.some(x => x.umur_group === 'UNKNOWN');

    const categories = hasUnknownUmur
      ? [...defaultCategories, 'UNKNOWN']
      : defaultCategories;


    const detectedStatuses = [
      ...new Set(
        dataSource.map(x =>
          String(x.status_umno ?? 'UNKNOWN').toUpperCase()
        )
      )
    ];


    const defaultBangsaGroups = ['Melayu', 'Cina', 'India', 'Lain-lain'];
    const hasUnknownBangsa = dataSource.some(x => x.bangsa_group === 'UNKNOWN');

    const bangsaGroups = hasUnknownBangsa
      ? [...defaultBangsaGroups, 'UNKNOWN']
      : defaultBangsaGroups;

    // -------------------------
    // Colors
    // -------------------------
    const baseColors = {
      'UMNO - Melayu': '#991b1b',
      'Bukan UMNO - Melayu': '#f87171',
      'UMNO - Cina': '#1e3a8a',
      'Bukan UMNO - Cina': '#60a5fa',
      'UMNO - India': '#854d0e',
      'Bukan UMNO - India': '#fbbf24',
      'UMNO - Lain-lain': '#047857',
      'Bukan UMNO - Lain-lain': '#6ee7b7',
      'UMNO - UNKNOWN': '#6b7280',
      'Bukan UMNO - UNKNOWN': '#9ca3af',
      'UNKNOWN - Melayu': '#6b7280',
      'UNKNOWN - Cina': '#6b7280',
      'UNKNOWN - India': '#6b7280',
      'UNKNOWN - Lain-lain': '#6b7280',
      'UNKNOWN - UNKNOWN': '#374151'
    };


    DashboardState.charts.bangsa = ensureChartRef(DashboardState.charts.bangsa);
    destroyChart(DashboardState.charts.bangsa);

    const buildSeries = (dataset, yearLabel = null, lighten = false) => {
      return bangsaGroups.flatMap(bangsa =>
        detectedStatuses.map(status => {

          let labelStatus;
          if (status === '1') labelStatus = 'UMNO';
          else if (status === '0') labelStatus = 'Bukan UMNO';
          else labelStatus = 'UNKNOWN';

          const name = `${yearLabel ? yearLabel + ' - ' : ''}${labelStatus} - ${bangsa}`;

          const data = categories.map(umur =>
            dataset
              .filter(x => {
                const umurGroup = (x.umur_group || 'UNKNOWN').toUpperCase();
                const bangsaGroup = (x.bangsa_group || 'UNKNOWN').toUpperCase();
                const statusUmno = String(x.status_umno ?? 'UNKNOWN');

                return (
                  umurGroup === umur.toUpperCase() &&
                  bangsaGroup === bangsa.toUpperCase() &&
                  statusUmno === status
                );
              })
              .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
          );


          const colorKey = `${labelStatus} - ${bangsa}`;
          const baseColor = baseColors[colorKey] || '#9ca3af';
          const color = lighten ? lightenColor(baseColor, 0.5) : baseColor;

          return { name, data, color };
        })
      );
    };

    if (mode === 'single') {
      const filtered = dataSource.filter(x => String(x.tarikh_undian) === String(year1));
      const series = buildSeries(filtered);

      await renderStackedBar(
        document.querySelector('#bangsaChart'),
        DashboardState.charts.bangsa,
        categories,
        series,
        'Jumlah Pengundi',
        'Umur',
        undefined,
        `Umur × Bangsa × Status UMNO`
      );
      return;
    }

    // Compare mode
    const filtered1 = dataSource.filter(x => String(x.tarikh_undian) === String(year1));
    const filtered2 = dataSource.filter(x => String(x.tarikh_undian) === String(year2));

    const series = [
      ...buildSeries(filtered1, year1),
      ...buildSeries(filtered2, year2, true)
    ];

    await renderStackedBar(
      document.querySelector('#bangsaChart'),
      DashboardState.charts.bangsa,
      categories,
      series,
      'Jumlah Pengundi',
      'Umur',
      undefined,
      `Perbandingan ${year1} vs ${year2} — Umur × Bangsa × Status UMNO`,
      false,
      true
    );
  }

</script>