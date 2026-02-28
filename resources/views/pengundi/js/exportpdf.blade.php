<script>
    
document.getElementById('exportPdf').addEventListener('click', async () => {
   toast.info("Exporting", "Generating PDF...");

  if (!DashboardState.charts) {
    alert('Charts not ready');
    return;
  }

  const spinner = document.getElementById('pdfSpinner');
  spinner.classList.remove('d-none'); // show spinner

  const images = [];

  for (const { chart, title } of Object.values(DashboardState.charts)) {
    if (!chart) continue;
    await new Promise(r => setTimeout(r, 300)); // ensure chart renders
    const { imgURI } = await chart.dataURI({ scale: 1 });
    images.push({ id: chart.w.globals.chartID, image: imgURI, title });
  }

  if (!images.length) {
    alert('No charts ready for export.');
    spinner.classList.add('d-none');
    return;
  }

  // Send to backend
  fetch('/pengundi/analytics/pdf', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ charts: images })
  })
  .then(res => res.blob())
  .then(blob => {
    window.open(URL.createObjectURL(blob));
  })
  .finally(() => {
    spinner.classList.add('d-none'); // hide spinner
    toast.success("PDF ready!");
  });
});




</script>