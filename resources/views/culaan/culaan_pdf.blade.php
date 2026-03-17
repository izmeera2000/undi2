<table width="100%" border="1" cellspacing="0" cellpadding="4"
    style="border-collapse:collapse;font-size:11px;table-layout:fixed">

    <thead>
        <tr style="background:#e8e8e8">
            <th width="10%">No</th>
            <th width="35%">Pengundi</th>
            <th width="25%">Lokaliti</th>
            <th width="20%">Details</th>
            <th width="10%">Culaan</th>
        </tr>
    </thead>

    <tbody>

        @php
            $startNumber = $counter ?? 1; // global row number passed from job

            $statuses = [
                'D' => 'BN',
                'A' => 'PH',
                'C' => 'PAS',
                'E' => 'TP',
                'O' => 'BC',
            ];
        @endphp

        @foreach ($rows as $i => $row)
            <tr>
                {{-- Global row number --}}
                <td>{{ $startNumber + $i }}</td>

                <td>
                    <strong>{{ $row['nama'] ?? '' }}</strong><br>
                    <small>{{ $row['no_kp'] ?? '' }}</small>
                </td>


                <td>
                    <strong>{{ $row['lokaliti'] ?? '' }}</strong><br>
                    <small>{{ $row['kod_lokaliti'] ?? '' }}</small>
                </td>

                <td>
                    {{ $row['kategori_pengundi'] ?? '' }}
                    @if(!empty($row['status_pengundi']))
                        ({{ $row['status_pengundi'] }})
                    @endif
                </td>
                <td style="text-align:center">
                    {{ $statuses[substr($row['status_culaan'] ?? '', 0, 1)] ?? $row['status_culaan'] ?? '' }}
                </td>
            </tr>
        @endforeach

    </tbody>
</table>