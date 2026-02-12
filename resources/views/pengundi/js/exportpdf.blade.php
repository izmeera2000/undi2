<script>
    
    document.getElementById('exportPdf').addEventListener('click', async () => {
      // console.log('Exporting PDF');

      const toast = new ToastMagic();
      toast.info("Exporting", "Exporting to PDF");


      if (!DashboardState.charts) {
        alert('Charts not ready');
        return;
      }


      await new Promise(r => setTimeout(r, 300));

      const images = [];

      for (const { chart, title } of Object.values(DashboardState.charts)) {
        if (!chart) continue;
        console.log(chart.core.w.config);

        const originalHeight = chart.core.w.config.chart.height;
        const originalWidth = chart.core.w.config.chart.width;
        const originalAnimated = chart.core.w.config.chart.animations.enabled;
        const totalCategories = chart.w.config.xaxis.categories.length;
        // console.log(originalAnimated);

        try {

          await chart.updateOptions({
            chart: {
              animations: { enabled: false },
            },
          });
          if (totalCategories >= 6) {
            try {
              await chart.updateOptions({
                chart: {
                  width: 600,
                },
              });
            } catch (error) {
              console.error('Error updating chart width:', error);
            }

            await new Promise(r => setTimeout(r, 1300));

          } else {
            await new Promise(r => setTimeout(r, 600));

          }

          // 3️⃣ Wait a moment for ApexCharts to render

          // 4️⃣ Capture image
          const { imgURI } = await chart.dataURI({ scale: 2 });
          images.push({
            id: chart.w.globals.chartID,
            image: imgURI,
            title,
          });

          if (totalCategories >= 6) {

            await chart.updateOptions({
              chart: {
                width: originalWidth,
                animations: { enabled: originalAnimated },

              },
            });
          }


        } catch (err) {
          console.warn('Chart not ready:', chart?.w?.globals?.chartID, err);
        }
      }

      if (!images.length) {
        alert('No charts ready for export yet.');
        return;
      }
      // console.log("start ex  ");

      // 6️⃣ Send to backend
      fetch('/pengundi/analytics/pdf', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ charts: images })
      })
        .then(res => res.blob())
        .then(blob => {
          window.open(URL.createObjectURL(blob));
        })
      // .then(() => console.log("end ex"));


    });





</script>