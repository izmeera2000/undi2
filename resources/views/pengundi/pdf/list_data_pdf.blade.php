<h2 style="margin-bottom:10px;">Senarai Pengundi Mengikut Lokaliti</h2>

<table border="1" width="100%" cellspacing="0" cellpadding="5" style="border-collapse:collapse;font-size:11px;">
    <!-- Maklumat Penapis -->
    <tr>
        <td><strong>Jenis PRU:</strong> {{ $filters['type'] ?? '-' }}</td>
        <td><strong>Series:</strong> {{ $filters['series'] ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>Parlimen:</strong> {{ $areaInfo->namapar ?? '-' }}</td>
        <td><strong>DUN:</strong> {{ $areaInfo->namadun ?? '-' }} ({{ $areaInfo->kod_dun ?? '-' }})</td>
    </tr>
    <tr>
        <td colspan="2"><strong>DM:</strong> {{ $areaInfo->namadm ?? '-' }} ({{ $areaInfo->koddm ?? '-' }})</td>
    </tr>
</table>

<br>

<table border="1" width="100%" cellspacing="0" cellpadding="5"
    style="border-collapse:collapse;font-size:11px;table-layout:fixed;">

    <thead style="display: table-header-group;">
        <tr style="background:#e8e8e8;">
            <th rowspan="2" width="4%">No</th>
            <th rowspan="2" width="20%">Lokaliti</th>
            <th colspan="{{ count($saluranList) }}">Saluran</th>
            <th rowspan="2" width="12%">Total</th>
        </tr>

        <tr style="background:#e8e8e8;">
            @foreach($saluranList as $saluran)
                <th width="{{ floor(60 / count($saluranList)) }}%">{{ $saluran }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @php $grandTotal = 0; @endphp

        @forelse ($data as $index => $row)
            @php $grandTotal += $row->total; @endphp
            <tr>
                <td align="center">{{ $index + 1 }}</td>
                <td><strong>{{ $row->nama_lokaliti }}</strong><br><small>{{ $row->kod_lokaliti }}</small></td>

                @foreach($saluranList as $saluran)
                    <td align="center">{{ $row->{'saluran_' . $saluran} ?? 0 }}</td>
                @endforeach

                <td align="center">{{ $row->total }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ 2 + count($saluranList) + 1 }}" align="center">Tiada data dijumpai</td>
            </tr>
        @endforelse

        @if($data->isNotEmpty())
            <tr style="background:#f5f5f5;font-weight:bold;">
                <td colspan="{{ 2 + count($saluranList) }}" align="right">GRAND TOTAL</td>
                <td align="center">{{ $grandTotal }}</td>
            </tr>
        @endif
    </tbody>

</table>