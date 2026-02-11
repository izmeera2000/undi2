  <script>

async function renderDmUmurChart() {
  const mode = document.getElementById('modeSelect').value;
  const year1 = document.getElementById('year1').value;
  const year2 = document.getElementById('year2').value;

  const umurGroups = ['18-20', '21-29', '30-39', '40-49', '50-59', '60+'];
  const allCategories = DashboardState.cube.map(x => x.namadm);
  const categories = [...new Set(allCategories)];
  if (!categories.length) return;

  DashboardState.charts.dun = DashboardState.charts.dun || { chart: null };
  if (DashboardState.charts.dun.chart) {
    DashboardState.charts.dun.chart.destroy();
    DashboardState.charts.dun.chart = null;
  }

  // Base colors for umur groups
  const baseColors = {
    '18-20': '#991b1b',
    '21-29': '#f87171',
    '30-39': '#1e3a8a',
    '40-49': '#60a5fa',
    '50-59': '#047857',
    '60+': '#6ee7b7'
  };

  const lightenColor = (hex, factor = 0.5) => {
    const r = parseInt(hex.substr(1, 2), 16);
    const g = parseInt(hex.substr(3, 2), 16);
    const b = parseInt(hex.substr(5, 2), 16);
    return `rgb(${Math.round(r + (255 - r) * factor)}, ${Math.round(g + (255 - g) * factor)}, ${Math.round(b + (255 - b) * factor)})`;
  };

  if (mode === 'single') {
    const cube = DashboardState.cube.filter(x => x.tarikh_undian === year1);

    const series = umurGroups.map(umur => ({
      name: umur,
      data: categories.map(dm =>
        cube
          .filter(x => x.namadm === dm && x.umur_group === umur)
          .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
      ),
      color: baseColors[umur]
    }));

    await renderStackedBar(
      document.querySelector('#dunChart'),
      DashboardState.charts.dun,
      categories,
      series,
      'Jumlah Pengundi', // Y-axis
      'DM',             // X-axis
      [],
      'DM × Umur',
      true,  // horizontal
      false
    );
    return;
  }

  // COMPARE MODE
  const cube1 = DashboardState.cube.filter(x => x.tarikh_undian === year1);
  const cube2 = DashboardState.cube.filter(x => x.tarikh_undian === year2);

  const series = umurGroups.flatMap(umur => [
    {
      name: `${year1} - ${umur}`,
      group: 'year1',
      data: categories.map(dm =>
        cube1
          .filter(x => x.namadm === dm && x.umur_group === umur)
          .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
      ),
      color: baseColors[umur]
    },
    {
      name: `${year2} - ${umur}`,
      group: 'year2',
      data: categories.map(dm =>
        cube2
          .filter(x => x.namadm === dm && x.umur_group === umur)
          .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
      ),
      color: lightenColor(baseColors[umur], 0.5)
    }
  ]);

  await renderStackedBar(
    document.querySelector('#dunChart'),
    DashboardState.charts.dun,
    categories,
    series,
    'Jumlah Pengundi',
    'DM',
    [],
    `Perbandingan ${year1} vs ${year2} — DM × Umur`,
    true,  // horizontal
    true   // grouped stacks
  );
}


  </script>
