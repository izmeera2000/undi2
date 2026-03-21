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
        'election_id',
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

    public function lokaliti()
    {
        return $this->belongsTo(Lokaliti::class, 'kod_lokaliti', 'kod_lokaliti');
    }

    public function dm()
    {
        return $this->hasOneThrough(
            Dm::class,
            Lokaliti::class,
            'kod_lokaliti', // FK on lokaliti → pengundi
            'kod_dm',       // PK on dm
            'kod_lokaliti', // local key on pengundi
            'kod_dm'        // local key on lokaliti
        );
    }


    public function election()
    {
        return $this->belongsTo(Election::class);
    }
}
