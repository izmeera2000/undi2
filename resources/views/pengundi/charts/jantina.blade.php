<script>

    
function renderJantinaChart() {
  const mode = document.getElementById('modeSelect').value;
  const year1 = document.getElementById('year1').value;
  const year2 = document.getElementById('year2').value;

  const categories = ['Lelaki', 'Perempuan'];

  // Base colors
  const baseColors = {
    'UMNO': '#991b1b',
    'Bukan UMNO': '#f87171'
  };

  const lightenColor = (hex, factor = 0.5) => {
    const r = parseInt(hex.substr(1, 2), 16);
    const g = parseInt(hex.substr(3, 2), 16);
    const b = parseInt(hex.substr(5, 2), 16);
    const newR = Math.round(r + (255 - r) * factor);
    const newG = Math.round(g + (255 - g) * factor);
    const newB = Math.round(b + (255 - b) * factor);
    return `rgb(${newR}, ${newG}, ${newB})`;
  };

  if (mode === 'single') {
    const cube = DashboardState.cube.filter(x => x.tarikh_undian === year1);

    const series = [
      {
        name: 'UMNO',
        data: categories.map(group =>
          cube
            .filter(x => x.jantina2 === group && x.status_umno === '1')
            .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
        ),
        color: baseColors['UMNO']
      },
      {
        name: 'Bukan UMNO',
        data: categories.map(group =>
          cube
            .filter(x => x.jantina2 === group && x.status_umno === '0')
            .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
        ),
        color: baseColors['Bukan UMNO']
      }
    ];

    renderStackedBar(
      document.querySelector('#jantinaChart'),
      DashboardState.charts.jantina,
      categories,
      series,
      'Jumlah Pengundi',
      'Jantina',
      [],
      'Jantina × Status UMNO'
    );

    return;
  }

  // 🔵 COMPARE MODE
  const cube1 = DashboardState.cube.filter(x => x.tarikh_undian === year1);
  const cube2 = DashboardState.cube.filter(x => x.tarikh_undian === year2);

  const series = [
    // Year 1 - full color
    {
      name: `${year1} - UMNO`,
      group: 'year1',
      data: categories.map(group =>
        cube1
          .filter(x => x.jantina2 === group && x.status_umno === '1')
          .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
      ),
      color: baseColors['UMNO']
    },
    {
      name: `${year1} - Bukan UMNO`,
      group: 'year1',
      data: categories.map(group =>
        cube1
          .filter(x => x.jantina2 === group && x.status_umno === '0')
          .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
      ),
      color: baseColors['Bukan UMNO']
    },
    // Year 2 - lighter / grayed out
    {
      name: `${year2} - UMNO`,
      group: 'year2',
      data: categories.map(group =>
        cube2
          .filter(x => x.jantina2 === group && x.status_umno === '1')
          .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
      ),
      color: lightenColor(baseColors['UMNO'], 0.5)
    },
    {
      name: `${year2} - Bukan UMNO`,
      group: 'year2',
      data: categories.map(group =>
        cube2
          .filter(x => x.jantina2 === group && x.status_umno === '0')
          .reduce((sum, x) => sum + (Number(x.total) || 0), 0)
      ),
      color: lightenColor(baseColors['Bukan UMNO'], 0.5)
    }
  ];

  renderStackedBar(
    document.querySelector('#jantinaChart'),
    DashboardState.charts.jantina,
    categories,
    series,
    'Jumlah Pengundi',
    'Jantina',
    [],
    `Perbandingan ${year1} vs ${year2} — Jantina × Status UMNO`,
    false, // horizontal
    true   // grouped stacks for comparison
  );
}
  
  
</script>