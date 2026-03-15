<table width="100%" cellspacing="0" cellpadding="5" border="0">
    <tr>
        <td width="90" align="center">
            <img src="data:image/png;base64,{{ $logo }}" width="70">
        </td>

        <td align="center">
            <font size="5"><b>{{ $culaan->name }}</b></font><br>

            <font size="3">
                Election :
                {{ $culaan->election_type }}
                {{ $culaan->election_number }}
                ({{ $culaan->election_year }})
            </font>
        </td>

        <td width="140" align="right">
            <font size="2">
                Generated<br>
                {{ $generatedAt }}
            </font>
        </td>
    </tr>
</table>

<hr>

<table width="100%" cellspacing="0" cellpadding="6" border="0">
    <tr>
        <td width="140"><b>Date</b></td>
        <td>: {{ $culaan->date }}</td>
    </tr>
</table>

<br>

<table width="100%" cellspacing="0" cellpadding="6" border="1">
    <tr bgcolor="#eeeeee">
        <td colspan="2"><b>Filters Applied</b></td>
    </tr>

    
    <tr>
        <td ><b>DM</b></td>
        <td>
            @if(empty($filters['dm']))
                All
            @else
                {{ $filters['dm_name'] }} ({{ $filters['dm'] }})
            @endif
        </td>
    </tr>


    <tr>
        <td ><b>Lokaliti</b></td>
        <td>
            @if(empty($filters['lokaliti']))
                All
            @else
                {{ $filters['lokaliti_name'] }} ({{ $filters['lokaliti'] }})
            @endif
        </td>
    </tr>

    @php
        $statuses = [
            'D' => 'BN',
            'A' => 'PH',
            'C' => 'PAS',
            'E' => 'TP',
            'O' => 'BC',
        ];
    @endphp
    <tr>
        <td><b>Status Culaan</b></td>
        <td>{{ $statuses[$filters['status_culaan']] ?? 'All' }}</td>
    </tr>

    <tr>
        <td><b>Search Name / IC</b></td>
        <td>{{ $filters['search_name'] ?? 'All' }}</td>
    </tr>

    <tr>
        <td><b>Total Pengundi Filtered</b></td>
        <td><b>{{ number_format($totalPengundi) }}</b></td>
    </tr>
</table>