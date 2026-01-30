<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengundiRaw extends Model
{
    protected $table = 'pengundi_raw';

    protected $fillable = [
        'kod_par','nama_par','kod_dun','nama_dun','koddm','namadm',
        'kod_lokaliti','nama_lokaliti','nokp_baru','nokp_lama','nama',
        'alamat_spr','bangsa','bangsa_spr','jantina','status_baru',
        'kodpar_pru12','tahun_lahir','umur','status_umno',
        'alamat_jpn1','alamat_jpn2','alamat_jpn3',
        'poskod','bandar','negeri'
    ];
}
