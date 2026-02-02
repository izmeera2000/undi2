<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'dun_id',
        'kod_cwgn',
        'nama_cwgn',
        'no_ahli',
        'nokp_baru',
        'nokp_lama',
        'nama',
        'tahun_lahir',
        'umur',
        'jantina',
        'alamat_1',
        'alamat_2',
        'alamat_3',
        'bangsa',
        'kod_dm',
        'alamat_jpn_1',
        'alamat_jpn_2',
        'alamat_jpn_3',
        'poskod',
        'bandar',
        'negeri',
    ];

    public function dun()
    {
        return $this->belongsTo(Dun::class);
    }
}
