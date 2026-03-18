<?php

namespace App\Imports;

use App\Models\MembersRaw;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Contracts\Queue\ShouldQueue;

 class MembersRawImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts, ShouldQueue
{
    public function model(array $row)
    {
        return new MembersRaw([
            'kod_bhgn'       => $row['kodbhgn'] ?? null,
            'nama_bhgn'      => $row['namabhgn'] ?? null,
            'kod_dun'        => $row['koddun'] ?? null,
            'nama_dun'       => $row['nama_dun'] ?? null,
            'kod_cwgn'       => $row['kodcwgn'] ?? null,
            'nama_cwgn'      => $row['namacwgn'] ?? null,
            'no_ahli'        => $row['no_ahli'] ?? null,
            'nokp_baru'      => $row['nokpbaru'] ?? null,
            'nokp_lama'      => $row['nokplama'] ?? null,
            'nama'           => $row['nama'] ?? null,
            'tahun_lahir'    => $row['tahunlahir'] ?? null,
            'umur'           => $row['umur'] ?? null,
            'jantina'        => $row['jantina'] ?? null,
            'alamat_1'       => $row['alamat_1'] ?? null,
            'alamat_2'       => $row['alamat_2'] ?? null,
            'alamat_3'       => $row['alamat_3'] ?? null,
            'bangsa'         => $row['bangsa_spr'] ?? null,
            'kod_dm'         => $row['kod_dm'] ?? null,
            'alamat_jpn_1'   => $row['alamat_jpn_1'] ?? null,
            'alamat_jpn_2'   => $row['alamat_jpn_2'] ?? null,
            'alamat_jpn_3'   => $row['alamat_jpn_3'] ?? null,
            'poskod'         => $row['poskod'] ?? null,
            'bandar'         => $row['bandar'] ?? null,
            'negeri'         => $row['negeri'] ?? null,
        ]);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
