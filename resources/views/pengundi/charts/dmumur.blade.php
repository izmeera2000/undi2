<script>

async function renderDmUmurChart() {
  const mode = document.getElementById('modeSelect').value;
  const year1 = document.getElementById('year1').value;
  const year2 = document.getElementById('year2').value;

  const umurGroups = ['18-20', '21-29', '30-39', '40-49', '50-59', '60+'];

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

  // 👉 Get unique DUN
  const allDuns = [...new Set(DashboardState.cube.map(x => x.namadun))];
  if (!allDuns.length) return;

  // Only take first 2 DUNs
  const selectedDuns = allDuns.slice(0, 2);

  for (let i = 0; i < selectedDuns.length; i++) {

    const dunName = selectedDuns[i];
    const container = document.querySelector(`#dunChart${i + 1}`);

    const cubeByDun = DashboardState.cube.filter(x => x.namadun === dunName);

    const categories = [...new Set(cubeByDun.map(x => x.namadm))];
    if (!categories.length) continue;

    DashboardState.charts[`dun${i}`] = DashboardState.charts[`dun${i}`] || { chart: null };

    if (DashboardState.charts[`dun${i}`].chart) {
      DashboardState.charts[`dun${i}`].chart.destroy();
      DashboardState.charts[`dun${i}`].chart = null;
    }

    // SINGLE MODE
    if (mode === 'single') {

      const cube = cubeByDun.filter(x => x.tarikh_undian === year1);

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
        container,
        DashboardState.charts[`dun${i}`],
        categories,
        series,
        'Jumlah Pengundi',
        'DM',
        [],
        `${dunName} — DM × Umur`,
        true,
        false
      );

    } else {

      const cube1 = cubeByDun.filter(x => x.tarikh_undian === year1);
      const cube2 = cubeByDun.filter(x => x.tarikh_undian === year2);

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
        container,
        DashboardState.charts[`dun${i}`],
        categories,
        series,
        'Jumlah Pengundi',
        'DM',
        [],
        `${dunName} — ${year1} vs ${year2}`,
        true,
        true
      );
    }
  }
}

</script>