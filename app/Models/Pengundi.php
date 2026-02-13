<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengundi extends Model
{
    protected $table = 'pengundi';

    protected $fillable = [
        'dm_id',
        'nokp_baru',
        'nokp_lama',
        'nama',
        'jantina',
        'bangsa',
        'umur',
        'tahun_lahir',
        'alamat_spr',
        'alamat_jpn_1',
        'alamat_jpn_2',
        'alamat_jpn_3',
        'poskod',
        'bandar',
        'negeri',
        'status_umno',
        'status_baru',
        'tarikh_undian',
    ];



    /**
     * Relationship: Pengundi belongs to DM
     */
    public function dm()
    {
        return $this->belongsTo(Dm::class);
    }






}
