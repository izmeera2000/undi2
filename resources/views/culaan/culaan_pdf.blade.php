<table width="100%" border="1" cellspacing="0" cellpadding="4">
    <thead>
        <tr>
            <th>ID</th>
            <th>Pengundi</th>
             <th>Lokaliti</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pengundi as $p)
            <tr>
                <td>{{ $p['id'] }}</td>

                <td>
                    <strong>{{ $p['nama'] }}</strong><br>
                    <small>{{ $p['no_kp'] }}</small>
                </td>
             
                <td>{{ $p['lokaliti_details'] }}</td>
                <td>{{ $p['status_culaan'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>