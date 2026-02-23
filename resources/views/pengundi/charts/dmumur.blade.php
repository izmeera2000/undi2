<script>
  async function renderDmUmurChart(payload) {
    console.log("🚀 Rendering DM × Umur Chart...");

    const { mode, type1, series1, type2, series2 } = payload;

    const umurGroups = ['18-20', '21-29', '30-39', '40-49', '50-59', '60+'];

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

    const dataset1 = DashboardState.dmUmurChart1 || [];
    const dataset2 = DashboardState.dmUmurChart2 || [];


    // Get all unique DUNs from both datasets
    const allDuns = [...new Set([...dataset1, ...dataset2].map(x => x.namadun))];

    // Use for...of to handle async/await correctly
    for (let i = 0; i < allDuns.slice(0, 2).length; i++) {
      const dunName = allDuns[i];
      const container = document.querySelector(`#dunChart${i + 1}`);
      if (!container) continue;

      const dunData1 = dataset1.filter(x => x.namadun === dunName);
      const dunData2 = dataset2.filter(x => x.namadun === dunName);

      const dmCategories = [...new Set([...dunData1, ...dunData2].map(x => x.namadm))];
      if (!dmCategories.length) {
        container.innerHTML = '<p>No DM data available.</p>';
        continue;
      }

      const chartKey = `dunChart${i + 1}`;
      DashboardState.charts[chartKey] = DashboardState.charts[chartKey] || { chart: null };
      if (DashboardState.charts[chartKey].chart) {
        DashboardState.charts[chartKey].chart.destroy();
        DashboardState.charts[chartKey].chart = null;
      }

      const buildSeries = (dataset, labelPrefix = null, lighten = false) => {
        return umurGroups.map(umur => ({
          name: labelPrefix ? `${labelPrefix} - ${umur}` : umur,
          data: dmCategories.map(dm =>
            dataset
              .filter(x => x.namadm === dm && x.umur_group === umur)
              .reduce((sum, x) => sum + Number(x.total || 0), 0)
          ),
          color: lighten ? lightenColor(baseColors[umur]) : baseColors[umur],
          stack: labelPrefix || 'single'
        }));
      };

      let series = [];
      if (mode === 'single' || !dataset2.length) {
        series = buildSeries(dunData1, ``, false);
        await renderStackedBar(
          container,
          DashboardState.charts[chartKey],
          dmCategories,
          series,
          'Jumlah Pengundi (%)',
          'DM',
          [],
          `${dunName} — DM × Umur (${type1} ${series1})`,
          true,
          false,
          true,

        );
      } else if (mode === 'compare') {
        series = [
          ...buildSeries(dunData1, `${type1} ${series1}`, false),
          ...buildSeries(dunData2, `${type2} ${series2}`, true)
        ];
        await renderStackedBar(
          container,
          DashboardState.charts[chartKey],
          dmCategories,
          series,
          'Jumlah Pengundi (%)',
          'DM',
          [],
          `${dunName} — ${type1} ${series1} vs ${type2} ${series2}`,
          true,
          true,
                          true,

        );
      }
    }

    // console.log("🚀 DM × Umur Chart render done");
  }
</script>