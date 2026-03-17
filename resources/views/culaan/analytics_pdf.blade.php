<!-- Charts -->
<div class="container">
    @foreach($charts as $index => $chart)
        <div class="chart-wrapper">
            <div class="chart-card">
                <img src="{{ $chart['image'] }}">
            </div>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</div>

<style>
    .page-break {
        page-break-before: always;
    }
</style>