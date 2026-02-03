<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pengundi Analytics PDF</title>
    <link href="{{ asset('assets/vendors/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        .page-break { page-break-after: always; }
        .chart-card { border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1.5rem; }
        .chart-title { font-size: 1.25rem; font-weight: 600; margin-bottom: 0.75rem; text-align: center; }
        img { width: 100%; height: auto; }
    </style>
</head>
<body>
<div class="container my-4">
    @foreach($charts as $chart)
        <div class="chart-card">
            @if(!empty($chart['title']))
                <div class="chart-title">{{ $chart['title'] }}</div>
            @else
                <div class="chart-title">{{ $chart['id'] }}</div>
            @endif

            <img src="{{ $chart['image'] }}" class="img-fluid">
        </div>
        <div class="page-break"></div>
    @endforeach
</div>
</body>
</html>
