<h2>Senarai Pengundi Mengikut Lokaliti</h2>

<table class="data-table" border="1" width="100%" cellspacing="0" cellpadding="5">

    <!-- Maklumat Penapis -->
    <tr>
        <td><strong>Jenis PRU:</strong> {{ $filters['type'] ?? '-' }}</td>
        <td><strong>Series:</strong> {{ $filters['series'] ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>Parlimen:</strong> {{ $filters['parlimen'] ?? '-' }}</td>
        <td><strong>DUN:</strong> {{ $filters['dun'] ?? '-' }}</td>
    </tr>
    <tr>
        <td colspan="2"><strong>DM:</strong> {{ $filters['dm'] ?? '-' }}</td>
    </tr>

    <!-- Spacer Row -->
    <tr>
        <td colspan="11">&nbsp;</td>
    </tr>

    <!-- Header Data -->
    <thead>
        <tr>
            <th rowspan="2">No</th>
            <th rowspan="2">Kod Lokaliti</th>
            <th rowspan="2" class="text-left">Nama Lokaliti</th>
            <th colspan="7">Saluran</th>
            <th rowspan="2">Total</th>
        </tr>
        <tr>
            @for ($i = 1; $i <= 7; $i++)
                <th>{{ $i }}</th>
            @endfor
        </tr>
    </thead>
    @php $grandTotal = 0; @endphp

    @forelse ($data as $index => $row)
        @php $grandTotal += $row->total; @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row->kod_lokaliti }}</td>
            <td class="text-left">{{ $row->nama_lokaliti }}</td>

            @for ($i = 1; $i <= 7; $i++)
                <td>{{ $row->{'saluran_' . $i} }}</td>
            @endfor

            <td>{{ $row->total }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="11" align="center">Tiada data dijumpai</td>
        </tr>
    @endforelse

    @if($data->isNotEmpty())
        <tr class="total-row">
            <td colspan="10" align="right"><strong>GRAND TOTAL</strong></td>
            <td><strong>{{ $grandTotal }}</strong></td>
        </tr>
    @endif

</table>