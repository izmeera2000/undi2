<script>
function renderJantinaChart() {
  console.log('--- renderJantinaChart START ---');

  const mode  = document.getElementById('modeSelect').value;
  const year1 = document.getElementById('year1').value;
  const year2 = document.getElementById('year2').value;

  const categories = ['Lelaki', 'Perempuan'];

  const baseColors = {
    'UMNO': '#991b1b',
    'Bukan UMNO': '#f87171'
  };

  const lightenColor = (hex, factor = 0.5) => {
    const r = parseInt(hex.substr(1, 2), 16);
    const g = parseInt(hex.substr(3, 2), 16);
    const b = parseInt(hex.substr(5, 2), 16);
    return `rgb(${Math.round(r + (255 - r) * factor)}, 
                ${Math.round(g + (255 - g) * factor)}, 
                ${Math.round(b + (255 - b) * factor)})`;
  };

  const dataSource = DashboardState.jantinaChart || [];

  const buildSeries = (dataset, yearLabel = null, lighten = false) => {
    const statuses = ['UMNO', 'Bukan UMNO'];

    return statuses.map(status => {

      const data = categories.map(gender => {
        return dataset
          .filter(x => x.jantina === gender && x.status_umno === status)
          .reduce((sum, x) => sum + Number(x.total || 0), 0);
      });

      const color = lighten 
        ? lightenColor(baseColors[status]) 
        : baseColors[status];

      return {
        name: yearLabel ? `${yearLabel} - ${status}` : status,
        data,
        color,
        stack: yearLabel || 'single'
      };
    });
  };

  let series = [];

  if (mode === 'single') {
    const filtered = dataSource.filter(x => String(x.tarikh_undian) === String(year1));
    series = buildSeries(filtered);
  } else {
    const filtered1 = dataSource.filter(x => String(x.tarikh_undian) === String(year1));
    const filtered2 = dataSource.filter(x => String(x.tarikh_undian) === String(year2));

    series = [
      ...buildSeries(filtered1, year1, false),
      ...buildSeries(filtered2, year2, true)
    ];
  }

  renderStackedBar(
    document.querySelector('#jantinaChart'),
    DashboardState.charts.jantina,
    categories,
    series,
    'Jumlah Pengundi',
    'Jantina',
    [],
    mode === 'single'
      ? `Jantina × Status UMNO (${year1})`
      : `Perbandingan ${year1} vs ${year2} — Jantina × Status UMNO`,
    false,
    true
  );

  console.log('--- renderJantinaChart END ---');
}
</script>
