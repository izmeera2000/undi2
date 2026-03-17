<!-- Header with logo, Culaan name, and generated timestamp -->
<table width="100%" cellspacing="0" cellpadding="5" border="0">
    <tr>
        <td width="90" align="center">
            <img src="data:image/png;base64,{{ $logo }}" width="70">
        </td>

        <td align="center">
            <span style="font-size:16pt; font-weight:bold;">{{ $culaan->name }}</span><br>
            <span style="font-size:11pt;">
                Election: {{ $culaan->election->type ?? '' }}
                {{ $culaan->election->number ?? '' }}
                ({{ $culaan->election->year ?? '' }}) </span>
        </td>

        <td width="140" align="right">
            <span style="font-size:9pt;">
                Generated:<br>{{ $generatedAt }}
            </span>
        </td>
    </tr>
</table>
<hr style="border:1px solid #000;">