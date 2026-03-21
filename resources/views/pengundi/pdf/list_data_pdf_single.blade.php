<table width="100%" border="1" cellspacing="0" cellpadding="4"
    style="border-collapse:collapse;font-size:11px;table-layout:fixed">
    <thead>
        <tr style="background:#e8e8e8">
            <th width="6%">No</th>
            <th width="34%">Pengundi</th>
            <th width="10%">Bangsa</th>
            <th width="10%">Jantina</th>
            <th width="8%">Saluran</th>
            <th width="32%">PM</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data[0]['details'] as $i => $p)
            <tr>
                {{-- Use the global number --}}
                <td>{{ $startNumber + $i }}</td>
                <td><strong>{{ $p->nama }}</strong><br><small>{{ $p->nokp_baru }}</small></td>
                <td>{{ ucfirst($p->bangsa)}}</td>
                <td>
                    @if($p->jantina === 'P')
                        Perempuan
                    @elseif($p->jantina === 'L')
                        Lelaki
                    @else
                        {{ ucfirst($p->jantina) }}
                    @endif
                </td>
                <td>{{ $p->saluran }}</td>
                <td>{{ $p->alamat_spr }}</td>
            </tr>
        @endforeach
    </tbody>
</table>