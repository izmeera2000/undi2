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

<h3>{{ $data[0]['nama_lokaliti'] }}</h3>

<table border="1" cellpadding="4" cellspacing="0">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Saluran</th>
            <th>No KP</th>
            <th>Bangsa</th>
            <th>Jantina</th>
            <th>Alamat</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data[0]['details'] as $i => $p)
            <tr>
                {{-- Use the global number --}}
                <td>{{ $startNumber + $i }}</td>
                <td>{{ $p->nama }}</td>
                <td>{{ $p->saluran }}</td>
                <td>{{ $p->nokp_baru }}</td>
                <td>{{ $p->bangsa }}</td>
                <td>{{ $p->jantina }}</td>
                <td>{{ $p->alamat_spr }}</td>
            </tr>
        @endforeach
    </tbody>
</table>


</body>

</html>