<table width="100%" border="1" cellspacing="0" cellpadding="4"
    style="border-collapse:collapse;font-size:11px;table-layout:fixed">

    <thead>
        <tr style="background:#e8e8e8">
            <th width="10%">No</th>
            <th width="35%">Pengundi</th>
            <th width="25%">Lokaliti</th>
            <th width="20%">Details</th>
            <th width="10%">Culaan</th>
        </tr>
    </thead>

    <tbody>

        @foreach ($rows as $i => $row)
            <tr>

                <td>{{ $counter + $i }}</td>

                <td>
                    <strong>{{ $row->nama }}</strong><br>
                    <small>{{ $row->no_kp }}</small>
                </td>

                <td>{{ $lokaliti }}</td>

                <td>{{ $row->details ?? '' }}</td>

                <td style="text-align:center">
                    {{ $row->status ?? '' }}
                </td>

            </tr>
        @endforeach

    </tbody>
</table>