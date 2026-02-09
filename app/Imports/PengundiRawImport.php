<?php

namespace App\Imports;



use App\Models\PengundiRaw;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
// use Illuminate\Contracts\Queue\ShouldQueue;

class PengundiRawImport implements
    ToModel,
    WithHeadingRow,
    WithChunkReading,
    WithBatchInserts 
{
    public function model(array $row)
    {
        return new PengundiRaw([
            'kod_par' => $row['kod_par'],
            'namapar' => $row['namapar'],
            'kod_dun' => $row['kod_dun'],
            'namadun' => $row['namadun'],
            'koddm' => $row['koddm'],
            'namadm' => $row['namadm'],
            'kodlokaliti' => $row['kodlokaliti'],
            'namalokaliti' => $row['namalokaliti'],
            'nokp_baru' => $row['nokp_baru'],
            'nokp_lama' => $row['nokp_lama'],
            'nama' => $row['nama'],
            'alamat_spr' => $row['alamat_spr'],
            'bangsa' => $row['bangsa'],
            'bangsa_spr' => $row['bangsa_spr'],
            'jantina' => $row['jantina'],
            'status_baru' => $row['status_baru'],
            'kodpar_pru12' => $row['kodpar_pru12'],
            'tahun_lahir' => $row['tahun_lahir'],
            'umur' => $row['umur'],
            'status_umno' => $row['status_umno'],
            'alamat_jpn_1' => $row['alamat_jpn_1'],
            'alamat_jpn_2' => $row['alamat_jpn_2'],
            'alamat_jpn_3' => $row['alamat_jpn_3'],
            'poskod' => $row['poskod'],
            'bandar' => $row['bandar'],
            'negeri' => $row['negeri'],
        ]);
    }

    public function chunkSize(): int
    {
    return 300;
    }

    public function batchSize(): int
    {
    return 300;
    }
}

