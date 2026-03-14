<style>
    /* Prevent a single row from being split across two pages */
    tr {
        page-break-inside: avoid !important;
    }

    /* Keep the header on every new page */
    thead {
        display: table-header-group;
    }

    /* Force the table to behave consistently */
    table {
        page-break-inside: auto;
        width: 100%;
        table-layout: fixed; /* Helps DomPDF calculate heights faster */
        word-wrap: break-word; /* Prevents long names from pushing column widths */
    }
    
    /* Ensure the name doesn't create infinite height */
    td {
        vertical-align: top;
    }
</style>

<table width="100%" border="1" cellspacing="0" cellpadding="4">

    <colgroup>
        <col width="6%">
        <col width="35%">
        <col width="25%">
        <col width="20%">
        <col width="14%">
    </colgroup>

    <thead>
        <tr>
            <th align="left">ID</th>
            <th align="left">Pengundi</th>
            <th align="left">Lokaliti</th>
            <th align="left">Details</th>
            <th align="left">Culaan</th>
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

            <td>{{ $p['pengundi_details'] }}</td>

            <td align="center">{{ $p['status_culaan'] }}</td>
        </tr>
        @endforeach
    </tbody>

</table>