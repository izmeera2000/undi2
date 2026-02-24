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
            <td>{{ $i + 1 }}</td>
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