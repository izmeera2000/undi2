<script>
async function renderBangsaChart() {
  const mode = document.getElementById('modeSelect').value;
  const year1 = document.getElementById('year1').value;
  const year2 = document.getElementById('year2').value;

  const categories = ['18-20', '21-29', '30-39', '40-49', '50-59', '60+'];
  const bangsaGroups = ['Melayu', 'Cina', 'India', 'Lain-lain'];

  const baseColors = {
    'UMNO - Melayu': '#991b1b',
    'Bukan UMNO - Melayu': '#f87171',
    'UMNO - Cina': '#1e3a8a',
    'Bukan UMNO - Cina': '#60a5fa',
    'UMNO - India': '#854d0e',
    'Bukan UMNO - India': '#fbbf24',
    'UMNO - Lain-lain': '#047857',
    'Bukan UMNO - Lain-lain': '#6ee7b7'
  };

  DashboardState.charts.bangsa = ensureChartRef(DashboardState.charts.bangsa);
  destroyChart(DashboardState.charts.bangsa);

  if (mode === 'single') {
    const cube = DashboardState.cube.filter(x => x.tarikh_undian === year1);

    const series = bangsaGroups.flatMap(bangsa => [
      {
        name: `UMNO - ${bangsa}`,
        data: categories.map(umur => 
          cube.filter(x => x.umur_group === umur && x.bangsa_group === bangsa && x.status_umno === '1')
              .reduce((sum,x) => sum + (Number(x.total) || 0),0)
        ),
        color: baseColors[`UMNO - ${bangsa}`]
      },
      {
        name: `Bukan UMNO - ${bangsa}`,
        data: categories.map(umur => 
          cube.filter(x => x.umur_group === umur && x.bangsa_group === bangsa && x.status_umno === '0')
              .reduce((sum,x) => sum + (Number(x.total) || 0),0)
        ),
        color: baseColors[`Bukan UMNO - ${bangsa}`]
      }
    ]);

    await renderStackedBar(
      document.querySelector('#bangsaChart'),
      DashboardState.charts.bangsa,
      categories,
      series,
      'Jumlah Pengundi',
      'Umur',
      [],
      `Umur × Bangsa × Status UMNO`
    );
    return;
  }

  // Compare mode
  const cube1 = DashboardState.cube.filter(x => x.tarikh_undian === year1);
  const cube2 = DashboardState.cube.filter(x => x.tarikh_undian === year2);

  const series = bangsaGroups.flatMap(bangsa => [
    // Year 1
    {
      name: `${year1} - UMNO - ${bangsa}`,
      group: 'year1',
      data: categories.map(umur =>
        cube1.filter(x => x.umur_group === umur && x.bangsa_group === bangsa && x.status_umno === '1')
             .reduce((sum,x) => sum + (Number(x.total) || 0),0)
      ),
      color: baseColors[`UMNO - ${bangsa}`]
    },
    {
      name: `${year1} - Bukan UMNO - ${bangsa}`,
      group: 'year1',
      data: categories.map(umur =>
        cube1.filter(x => x.umur_group === umur && x.bangsa_group === bangsa && x.status_umno === '0')
             .reduce((sum,x) => sum + (Number(x.total) || 0),0)
      ),
      color: baseColors[`Bukan UMNO - ${bangsa}`]
    },
    // Year 2 - grayed
    {
      name: `${year2} - UMNO - ${bangsa}`,
      group: 'year2',
      data: categories.map(umur =>
        cube2.filter(x => x.umur_group === umur && x.bangsa_group === bangsa && x.status_umno === '1')
             .reduce((sum,x) => sum + (Number(x.total) || 0),0)
      ),
      color: lightenColor(baseColors[`UMNO - ${bangsa}`],0.5)
    },
    {
      name: `${year2} - Bukan UMNO - ${bangsa}`,
      group: 'year2',
      data: categories.map(umur =>
        cube2.filter(x => x.umur_group === umur && x.bangsa_group === bangsa && x.status_umno === '0')
             .reduce((sum,x) => sum + (Number(x.total) || 0),0)
      ),
      color: lightenColor(baseColors[`Bukan UMNO - ${bangsa}`],0.5)
    }
  ]);

  await renderStackedBar(
    document.querySelector('#bangsaChart'),
    DashboardState.charts.bangsa,
    categories,
    series,
    'Jumlah Pengundi',
    'Umur',
    [],
    `Perbandingan ${year1} vs ${year2} — Umur × Bangsa × Status UMNO`,
    false,
    true
  );
}

</script>