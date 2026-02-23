<!DOCTYPE html>
<html>

<head>
    <title>Senarai Pengundi</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        th {
            background: #eee;
        }

        .filter {
            font-size: 12px;
            margin-bottom: 10px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <h3>Senarai Pengundi</h3>
    <div class="filter">
        PR Type: {{ $filters['type'] }}, Series: {{ $filters['series'] }}<br>
        Parlimen: {{ $filters['parlimen'] }}, DUN: {{ $filters['dun'] }}, DM: {{ $filters['dm'] }}
    </div>

  @foreach ($data as $lokaliti)
    <h3>{{ $lokaliti['nama_lokaliti'] }} ({{ count($lokaliti['details']) }} voters)</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Name</th>
                <th>No KP</th>
                <th>Saluran</th>
                <th>Bangsa</th>
                <th>Jantina</th>
                <th>Alamat SPR</th>
            </tr>
        </thead>
        <tbody>
@foreach ($lokaliti['details']->take(50) as $i => $pengundi)
    <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $pengundi['nama'] ?? '' }}</td>
        <td>{{ $pengundi['nokp_baru'] ?? '' }}</td>
        <td>{{ $pengundi['saluran'] ?? '' }}</td>
        <td>{{ $pengundi['bangsa'] ?? '' }}</td>
        <td>{{ $pengundi['jantina'] ?? '' }}</td>
        <td>{{ $pengundi['alamat_spr'] ?? '' }}</td>
    </tr>
@endforeach
        </tbody>
    </table>
    <div style="page-break-after: always;"></div>
@endforeach
</body>

</html>