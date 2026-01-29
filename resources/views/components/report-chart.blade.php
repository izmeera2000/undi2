 




<div class="card">

    <div class="filter">
        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <li class="dropdown-header text-start">
                <h6>Filter</h6>
            </li>

            <li><a class="dropdown-item" href="#">Today</a></li>
            <li><a class="dropdown-item" href="#">This Month</a></li>
            <li><a class="dropdown-item" href="#">This Year</a></li>
        </ul>
    </div>

    <div class="card-body">
        <h5 class="card-title">{{ $title }}</span></h5>

        <!-- Line Chart -->
        <div id="{{ $chartId }}"></div>

     
        <!-- End Line Chart -->

    </div>

</div>


<script>
document.addEventListener("DOMContentLoaded", function () {

    fetch("{{ $url }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({})
    })
    .then(res => res.json())
    .then(response => {

        new ApexCharts(document.querySelector("#{{ $chartId }}"), {
            series: response.series,
            chart: {
                height: 350,
                type: 'area',
                toolbar: { show: false }
            },
            markers: { size: 4 },
            colors: ['#4154f1', '#2eca6a', '#ff771d'],
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.4,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            xaxis: {
                type: 'datetime',
                categories: response.categories
            },
            tooltip: {
                x: { format: 'dd/MM/yy HH:mm' }
            }
        }).render();

    });
});
</script>
