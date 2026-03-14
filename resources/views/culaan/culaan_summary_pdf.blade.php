<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #333;
    }

    .header-table {
        width: 100%;
        border-bottom: 2px solid #444;
        margin-bottom: 15px;
    }

    .logo {
        width: 80px;
    }

    .title {
        font-size: 20px;
        font-weight: bold;
        text-align: center;
    }

    .subtitle {
        font-size: 13px;
        text-align: center;
    }

    .info-table {
        width: 100%;
        margin-top: 10px;
        border-collapse: collapse;
    }

    .info-table td {
        padding: 4px 6px;
        vertical-align: top;
    }

    .filters {
        margin-top: 10px;
        border: 1px solid #ccc;
    }

    .filters th {
        background: #f2f2f2;
        text-align: left;
        padding: 6px;
    }

    .filters td {
        padding: 6px;
    }
</style>


<table class="header-table">
    <tr>
        <td width="90">
            <img src="{{ public_path('assets/img/UMNO_logo.png') }}" width="80">
        </td>

        <td>
            <div class="title">
                {{ $culaan->name }}
            </div>

            <div class="subtitle">
                Election :
                {{ $culaan->election_type }}
                {{ $culaan->election_number }}
                ({{ $culaan->election_year }})
            </div>
        </td>

        <td width="120" style="text-align:right; font-size:11px;">
            Generated<br>
            {{ \Carbon\Carbon::now('Asia/Kuala_Lumpur')->format('d M Y H:i') }}
        </td>
    </tr>
</table>


<table class="info-table">
    <tr>
        <td width="120"><strong>Date</strong></td>
        <td>: {{ $culaan->date }}</td>
    </tr>
</table>


<table class="filters" width="100%" cellspacing="0">
    <tr>
        <th colspan="2">Filters Applied</th>
    </tr>

    <tr>
        <td width="150"><strong>Lokaliti</strong></td>
        <td>{{ $filters['lokaliti'] ?? 'All' }}</td>
    </tr>

    <tr>
        <td><strong>Status Culaan</strong></td>
        <td>{{ $filters['status_culaan'] ?? 'All' }}</td>
    </tr>

    <tr>
        <td><strong>Search Name / IC</strong></td>
        <td>{{ $filters['search_name'] ?? 'All' }}</td>
    </tr>
    <tr>
        <td><strong>Total Pengundi Filtered</strong></td>
        <td>{{ number_format($totalPengundi) }}</td>
    </tr>

</table>