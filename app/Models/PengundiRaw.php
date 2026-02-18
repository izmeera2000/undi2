<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengundiRaw extends Model
{
    protected $table = 'pengundi_raw';

    protected $fillable = [
        'kod_par',
        'namapar',
        'kod_dun',
        'namadun',
        'koddm',
        'namadm',
        'kodlokaliti',
        'namalokaliti',
        'nokp_baru',
        'nokp_lama',
        'nama',
        'alamat_spr',
        'bangsa',
        'bangsa_spr',
        'jantina',
        'status_baru',
        'kodpar_pru12',
        'tahun_lahir',
        'umur',
        'status_umno',
        'alamat_jpn_1',
        'alamat_jpn_2',
        'alamat_jpn_3',
        'poskod',
        'bandar',
        'negeri',
        'saluran',
        'no_siri',
        'pilihan_raya_type',
        'pilihan_raya_series',
    ];
}
