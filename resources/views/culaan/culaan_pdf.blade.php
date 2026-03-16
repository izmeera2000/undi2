<style>
 

    table {
        border-collapse: collapse;
        font-size: 12px;
        table-layout: auto;
    }

    thead th {
        background-color: #e8e8e8;
        padding: 4px;
    }

    td {
        padding: 4px;
        vertical-align: top;
    }

</style>

<table width="100%" border="1" cellspacing="0" cellpadding="6"
       style="border-collapse:collapse; table-layout:fixed; font-size:12px;">

    <thead>
        <tr>
            <th style="width:6%;">ID</th>
            <th style="width:35%;">Pengundi</th>
            <th style="width:25%;">Lokaliti</th>
            <th style="width:20%;">Details</th>
            <th style="width:14%; text-align:center;">Culaan</th>
        </tr>
    </thead>

    <tbody>
        @foreach($pengundi as $p)
        <tr>

            <td>{{ $p['id'] }}</td>

            <td>
                <div style="word-break:break-word;">
                    <strong>{{ $p['nama'] }}</strong><br>
                    <small>{{ $p['no_kp'] }}</small>
                </div>
            </td>

            <td>
                <div style="word-break:break-word;">
                    {{ $p['lokaliti_details'] }}
                </div>
            </td>

            <td>
                <div style="word-break:break-word;">
                    {{ $p['pengundi_details'] }}
                </div>
            </td>

            <td style="text-align:center;">
                {{ $p['status_culaan'] }}
            </td>

        </tr>
        @endforeach
    </tbody>

</table>