<style>

    header {
        position: fixed;
        top: -40px; /* negative to pull above content */
        left: 0;
        right: 0;
        height: 40px;
        text-align: left;
        font-size: 16px;
        font-weight: bold;
        padding: 5px 0;
        background-color: #f0f0f0;
        border: 1px solid #ccc;
    }

 
</style>


<header>
    PM: {{ $pm ?? 'N/A' }}
</header>

<table width="100%" border="1" cellspacing="0" cellpadding="6"
       style="border-collapse: collapse; font-size:12px; table-layout: auto;">
    <colgroup>
        <col style="width:6%;">
        <col style="width:35%;">
        <col style="width:25%;">
        <col style="width:20%;">
        <col style="width:14%;">
    </colgroup>

    <thead>
        <tr style="background-color:#e8e8e8;">
            <th style="padding:4px;">ID</th>
            <th style="padding:4px;">Pengundi</th>
            <th style="padding:4px;">Lokaliti</th>
            <th style="padding:4px;">Details</th>
            <th style="padding:4px; text-align:center;">Culaan</th>
        </tr>
    </thead>

    <tbody>
        @foreach($pengundi as $p)
            <tr>
                <td style="padding:4px;">{{ $p['id'] }}</td>

                <td style="padding:4px;">
                    <div style="max-width:200px; word-wrap: break-word; overflow-wrap: break-word;">
                        <strong>{{ $p['nama'] }}</strong><br>
                        <small>{{ $p['no_kp'] }}</small>
                    </div>
                </td>

                <td style="padding:4px;">
                    <div style="max-width:150px; word-wrap: break-word; overflow-wrap: break-word;">
                        {{ $p['lokaliti_details'] }}
                    </div>
                </td>

                <td style="padding:4px;">
                    <div style="max-width:120px; word-wrap: break-word; overflow-wrap: break-word;">
                        {{ $p['pengundi_details'] }}
                    </div>
                </td>

                <td style="padding:4px; text-align:center;">
                    {{ $p['status_culaan'] }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>