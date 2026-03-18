<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengundi extends Model
{
    protected $table = 'pengundi';

    protected $fillable = [
        'kod_lokaliti',
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
        'saluran',
        'no_siri',
        'type_data_id',
        'pilihan_raya_type',
        'pilihan_raya_series',
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
        return $this->belongsTo(Lokaliti::class, 'kod_lokaliti', 'kod_lokaliti');
    }

    // Access DM through Lokaliti
    // Access DM through Lokaliti (lokaliti.kod_dm → dm.kod_dm)
    public function dm()
    {
        return $this->hasOneThrough(
            Dm::class,          // final model
            Lokaliti::class,    // intermediate model
            'kod_dm',            // FK on Dm? No, intermediate FK is lokaliti.kod_dm
            'kod_dm',            // PK on Dm = kod_dm
            'kod_lokaliti',     // Local key on Pengundi
            'kod_dm'             // Local key on Lokaliti
        );
    }


    // Access DUN through DM
    public function dun()
    {
        return $this->hasOneThrough(
            Dun::class,
            Dm::class,
            'kod_dun',     // FK on Dm = kod_dun
            'kod_dun',     // PK on Dun = kod_dun
            'kod_dm',       // Local key on Pengundi? Actually via dm
            'kod_dun'      // Local key on DM
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
