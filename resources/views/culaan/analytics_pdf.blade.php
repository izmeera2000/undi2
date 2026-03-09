<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Pengundi Analytics PDF</title>
    <link href="{{ asset('assets/vendors/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        @page {
            margin: 120px 50px 80px 50px;
            /* top, right, bottom, left */
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }

        /* Header */
        header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            height: 100px;
            text-align: center;
            line-height: 1.2;
            border-bottom: 1px solid #ccc;
        }

        /* Footer */
        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 60px;
            text-align: center;
            font-size: 10px;
            color: #555;
            border-top: 1px solid #ccc;
        }

        .page-number:before {
            content: "Page " counter(page);
        }

        /* Chart styling */
        .chart-card {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .chart-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            text-align: center;
        }

        img {
            width: 100%;
            height: auto;
        }

        /* Optional 7/5 column layout for desktop */
        .row-cols-pdf {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.5rem;
        }

        .col-7-pdf {
            width: 58%;
            padding: 0 0.5rem;
            box-sizing: border-box;
        }

        .col-5-pdf {
            width: 40%;
            padding: 0 0.5rem;
            box-sizing: border-box;
        }

        .chart-card {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;

            /* CRITICAL for PDF */
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .chart-card img {
            display: block;

            max-width: 100% !important;
            width: auto !important;

            max-height: 850px !important;
            /* SAFE height for A4 portrait */
            height: auto !important;

            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }


        .chart-wrapper {
            page-break-inside: avoid !important;
        }


        /* Force new page when card is too tall */
        .chart-card {
            page-break-before: auto;
        }
    </style>

</head>

<body>
    <header>
        <h2>Culaan Report</h2>
        <p>{{ \Carbon\Carbon::now()->format('d M Y') }}</p>
    </header>
    <div class="container my-4">
        @foreach($charts as $chart)
            <div class="chart-wrapper">
                <div class="chart-card">
                    <img src="{{ $chart['image'] }}">
                </div>
            </div>
        @endforeach

    </div>

    <footer>
        <span class="page-number"></span>
    </footer>
</body>

</html>