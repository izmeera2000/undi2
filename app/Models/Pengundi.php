<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengundi extends Model
{
    protected $table = 'pengundi';

    protected $fillable = [
        'kod_lokaliti', // 🔥 changed from dm_id
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

    protected $casts = [
        'tarikh_undian' => 'integer', // treat as YEAR

        'tahun_lahir' => 'integer',
        'umur' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Pengundi belongs to Lokaliti
    public function lokaliti()
    {
        return $this->belongsTo(Lokaliti::class);
    }

    // Access DM through Lokaliti
    public function dm()
    {
        return $this->hasOneThrough(
            Dm::class,
            Lokaliti::class,
            'id',       // FK on lokaliti table
            'id',       // FK on dm table
            'lokaliti_id',
            'dm_id'
        );
    }

    // Access DUN through DM
    public function dun()
    {
        return $this->hasOneThrough(
            Dun::class,
            Dm::class,
            'id',
            'id',
            'lokaliti_id',
            'dun_id'
        );
    }

    // Access Parlimen through DUN
    public function parlimen()
    {
        return $this->hasOneThrough(
            Parlimen::class,
            Dun::class,
            'id',
            'id',
            'lokaliti_id',
            'parlimen_id'
        );
    }
}
