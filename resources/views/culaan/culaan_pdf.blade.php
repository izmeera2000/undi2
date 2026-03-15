<style>
    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        /* Crucial for fixed column widths */
    }

    th,
    td {
        border: 1px solid #000;
        padding: 4px;
        word-wrap: break-word;
        /* Forces long text to wrap */
        vertical-align: top;
        font-size: 11px;
        /* Smaller font helps with tight tables */
    }

    tr {
        page-break-inside: avoid;
    }

    thead {
        display: table-header-group;
        /* Repeats header on every page */
    }
</style>
<table border="1">
    <colgroup>
        <col style="width: 5%;">
        <col style="width: 35%;">
        <col style="width: 25%;">
        <col style="width: 25%;">
        <col style="width: 10%;">
    </colgroup>
    <thead>
        <tr>
            <th>ID</th>
            <th>Pengundi</th>
            <th>Lokaliti</th>
            <th>Details</th>
            <th>Culaan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pengundi as $p)
            <tr>
                <td>{{ $p['id'] }}</td>
                <td>
                    <strong>{{ $p['nama'] }}</strong><br>
                    <span style="font-size: 9px; color: #555;">{{ $p['no_kp'] }}</span>
                </td>
                <td>{{ $p['lokaliti_details'] }}</td>
                <td>{{ $p['pengundi_details'] }}</td>
                <td align="center">{{ $p['status_culaan'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>