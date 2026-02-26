<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Senarai Pengundi Mengikut Lokaliti</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 2px 4px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.data-table th,
        table.data-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        table.data-table th {
            background: #f0f0f0;
        }

        .text-left {
            text-align: left;
        }

        .total-row {
            font-weight: bold;
            background: #f9f9f9;
        }
    </style>
</head>

<body>

    <h2>Senarai Pengundi Mengikut Lokaliti</h2>

    <table class="info-table">
        <tr>
            <td><strong>Jenis PRU:</strong> {{ $type }}</td>
            <td><strong>Series:</strong> {{ $series }}</td>
        </tr>
        <tr>
            <td><strong>Parlimen:</strong> {{ $parlimenName ?? $parlimen }}</td>
            <td><strong>DUN:</strong> {{ $dunName ?? $dun }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>DM:</strong> {{ $dmName ?? $dm }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kod Lokaliti</th>
                <th class="text-left">Nama Lokaliti</th>
                @for ($i = 1; $i <= 7; $i++)
                    <th>S{{ $i }}</th>
                @endfor
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp

            @foreach ($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->kod_lokaliti }}</td>
                    <td>{{ $row->nama_lokaliti }}</td>
                    <td>{{ $row->saluran_1 }}</td>
                    <td>{{ $row->saluran_2 }}</td>
                    <td>{{ $row->saluran_3 }}</td>
                    <td>{{ $row->saluran_4 }}</td>
                    <td>{{ $row->saluran_5 }}</td>
                    <td>{{ $row->saluran_6 }}</td>
                    <td>{{ $row->saluran_7 }}</td>
                    <td>{{ $row->total }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="10">GRAND TOTAL</td>
                <td>{{ $grandTotal }}</td>
            </tr>
        </tbody>
    </table>

</body>

</html>