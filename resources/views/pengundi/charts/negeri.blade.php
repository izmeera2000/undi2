<script>

async function renderNegeriChart() {
  const mode = document.getElementById('modeSelect').value;
  const year1 = document.getElementById('year1').value;
  const year2 = document.getElementById('year2').value;

  // Get unique categories from cube
  const allCategories = DashboardState.cube.map(x => x.negeri ? x.negeri.trim() : 'UNKNOWN');
  const categories = [...new Set(allCategories)];
  if (!categories.length) return;

  // Stacks & base colors
  const stacks = [
    { umno: '1', baru: '1', name: 'UMNO - First Time', color: '#991b1b' },
    { umno: '1', baru: '0', name: 'UMNO - Existing', color: '#f87171' },
    { umno: '0', baru: '1', name: 'Bukan UMNO - First Time', color: '#1e3a8a' },
    { umno: '0', baru: '0', name: 'Bukan UMNO - Existing', color: '#60a5fa' },
  ];

  DashboardState.charts.negeri = DashboardState.charts.negeri || { chart: null };
  if (DashboardState.charts.negeri.chart) {
    DashboardState.charts.negeri.chart.destroy();
    DashboardState.charts.negeri.chart = null;
  }

  const lightenColor = (hex, factor = 0.5) => {
    const r = parseInt(hex.substr(1, 2), 16);
    const g = parseInt(hex.substr(3, 2), 16);
    const b = parseInt(hex.substr(5, 2), 16);
    return `rgb(${Math.round(r + (255 - r) * factor)}, ${Math.round(g + (255 - g) * factor)}, ${Math.round(b + (255 - b) * factor)})`;
  };

  // SINGLE YEAR MODE
  if (mode === 'single') {
    const cube = DashboardState.cube.filter(x => x.tarikh_undian === year1);

    const series = stacks.map(stack => ({
      name: stack.name,
      data: categories.map(negeri =>
        cube
          .filter(x => (x.negeri ? x.negeri.trim() : 'UNKNOWN') === negeri &&
                       x.status_umno === stack.umno &&
                       x.status_baru === stack.baru)
          .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
      ),
      color: stack.color
    }));

    await renderStackedBar(
      document.querySelector('#negeriChart'),
      DashboardState.charts.negeri,
      categories,
      series,
      'Jumlah Pengundi',
      'Negeri',
      [],
      'Negeri × UMNO × First Time',
      true,
      false
    );
    return;
  }

  // COMPARE MODE
  const cube1 = DashboardState.cube.filter(x => x.tarikh_undian === year1);
  const cube2 = DashboardState.cube.filter(x => x.tarikh_undian === year2);

  const series = stacks.flatMap(stack => [
    // Year1
    {
      name: `${year1} - ${stack.name}`,
      group: 'year1',
      data: categories.map(negeri =>
        cube1
          .filter(x => (x.negeri ? x.negeri.trim() : 'UNKNOWN') === negeri &&
                       x.status_umno === stack.umno &&
                       x.status_baru === stack.baru)
          .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
      ),
      color: stack.color
    },
    // Year2 - lighter
    {
      name: `${year2} - ${stack.name}`,
      group: 'year2',
      data: categories.map(negeri =>
        cube2
          .filter(x => (x.negeri ? x.negeri.trim() : 'UNKNOWN') === negeri &&
                       x.status_umno === stack.umno &&
                       x.status_baru === stack.baru)
          .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
      ),
      color: lightenColor(stack.color, 0.5)
    }
  ]);

  await renderStackedBar(
    document.querySelector('#negeriChart'),
    DashboardState.charts.negeri,
    categories,
    series,
    'Jumlah Pengundi',
    'Negeri',
    [],
    `Perbandingan ${year1} vs ${year2} — Negeri × UMNO × First Time`,
    true,   // horizontal
    true    // grouped stacks
  );
}
    
</script>