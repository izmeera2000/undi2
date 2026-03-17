<!-- Header with logo, Culaan name, and generated timestamp -->
<table width="100%" cellspacing="0" cellpadding="5" border="0">
    <tr>
        <td width="90" align="center">
            <img src="data:image/png;base64,{{ $logo }}" width="70">
        </td>

        <td align="center">
            <span style="font-size:16pt; font-weight:bold;">{{ $culaan->name }}</span><br>
            <span style="font-size:11pt;">
                Election: {{ $culaan->election_type }} {{ $culaan->election_number }} ({{ $culaan->election_year }})
            </span>
        </td>

        <td width="140" align="right">
            <span style="font-size:9pt;">
                Generated:<br>{{ $generatedAt }}
            </span>
        </td>
    </tr>
</table>

<hr style="border:1px solid #000;">

<!-- Culaan Date -->
<table width="100%" cellspacing="0" cellpadding="6" border="0">
    <tr>
        <td width="140"><b>Date</b></td>
        <td>: {{ $culaan_date }}</td>
    </tr>
</table>

<br>

<!-- Filters Table -->
<table width="100%" cellspacing="0" cellpadding="6" border="1" style="border-collapse:collapse; font-size:10pt;">
    <tr bgcolor="#eeeeee">
        <td colspan="2" style="font-weight:bold;">Filters Applied</td>
    </tr>

    <tr>
        <td width="140"><b>DM</b></td>
        <td>
            @if(empty($filters['dm']))
                All
            @else
                {{ $filters['dm_name'] }} ({{ $filters['dm'] }})
            @endif
        </td>
    </tr>

    <tr>
        <td><b>Lokaliti</b></td>
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

<br>

<!-- Table of Contents -->
<table width="100%" border="1" cellspacing="0" cellpadding="5" style="border-collapse:collapse; font-size:10pt;">
    <tr bgcolor="#eeeeee">
        <td><b>Table of Contents</b></td>
        <td width="60" align="center"><b>Page</b></td>
    </tr>

    @foreach($toc as $item)
        <tr>
            <td>{{ $item['pm'] }}</td>
            <td align="center">{{ $item['start_page'] }}</td>
        </tr>
    @endforeach
</table>