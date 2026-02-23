<script>
window.renderNegeriChart = async function(payload) {
  console.log('🚀 Rendering Negeri × UMNO Chart...');
  
  // Destructure payload metadata
  const { mode, type1, series1, type2, series2 } = payload;

  // Use datasets stored in DashboardState
  const dataset1 = DashboardState.negeriChart1 || [];
  const dataset2 = DashboardState.negeriChart2 || [];

  // Extract all unique negeri from both datasets
  const allNegeri = [...new Set([...dataset1, ...dataset2].map(x => x.negeri ? x.negeri.trim() : 'UNKNOWN'))];
  if (!allNegeri.length) return console.warn('No Negeri categories found.');

  // Define stacks
  const stacks = [
    { umno: '1', baru: '1', label: 'UMNO - First Time', color: '#991b1b' },
    { umno: '1', baru: '0', label: 'UMNO - Existing', color: '#f87171' },
    { umno: '0', baru: '1', label: 'Bukan UMNO - First Time', color: '#1e3a8a' },
    { umno: '0', baru: '0', label: 'Bukan UMNO - Existing', color: '#60a5fa' }
  ];

  // Function to lighten color for compare mode
  const lightenColor = (hex, factor = 0.5) => {
    const r = parseInt(hex.substr(1,2),16);
    const g = parseInt(hex.substr(3,2),16);
    const b = parseInt(hex.substr(5,2),16);
    return `rgb(${Math.round(r + (255 - r) * factor)}, ${Math.round(g + (255 - g) * factor)}, ${Math.round(b + (255 - b) * factor)})`;
  };

  // Destroy previous chart if exists
  DashboardState.charts.negerichart = DashboardState.charts.negerichart || { chart: null };
  if (DashboardState.charts.negerichart.chart) {
    DashboardState.charts.negerichart.chart.destroy();
    DashboardState.charts.negerichart.chart = null;
  }

  // Build series function
  const buildSeries = (dataset, labelPrefix = null, lighten = false) => {
    return stacks.map(stack => {
      const data = allNegeri.map(negeri => {
        return dataset
          .filter(x =>
            (x.negeri ? x.negeri.trim() : 'UNKNOWN') === negeri &&
            x.status_umno === stack.umno &&
            x.status_baru === stack.baru
          )
          .reduce((sum, x) => sum + Number(x.total || 0), 0);
      });

      return {
        name: labelPrefix ? `${labelPrefix} - ${stack.label}` : stack.label,
        data,
        color: lighten ? lightenColor(stack.color) : stack.color,
        stack: labelPrefix || 'single'
      };
    });
  };

  // Build final series array
  let series = [];
  if (mode === 'single' || !dataset2.length) {
    series = buildSeries(dataset1, `${type1} ${series1}`, false);
  } else if (mode === 'compare') {
    series = [
      ...buildSeries(dataset1, `${type1} ${series1}`, false),
      ...buildSeries(dataset2, `${type2} ${series2}`, true)
    ];
  }

  console.log('Series ready for chart:', series);

  // Render chart
  await renderStackedBar(
    document.querySelector('#negeriChart'),
    DashboardState.charts.negeri,
    allNegeri,
    series,
    'Jumlah Pengundi',
    'Negeri',
    [],
    mode === 'single'
      ? `Negeri × UMNO × First Time (${type1} ${series1})`
      : `Perbandingan ${type1} ${series1} vs ${type2} ${series2} — Negeri × UMNO × First Time`,
    true,  // horizontal
    mode === 'compare' // group by year if compare
  );

  console.log('🚀 Negeri × UMNO Chart render done');
};
</script>