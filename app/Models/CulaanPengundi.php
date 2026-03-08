<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CulaanPengundi extends Model
{
    protected $table = 'culaan_pengundis';

    protected $fillable = [
        'culaan_id',

        'kod_lokaliti',
        'lokaliti',
        'pm',

        'no_siri',
        'saluran',

        'nama',
        'no_kp',

        'jantina',
        'umur',
        'bangsa',

        'kategori_pengundi',
        'status_pengundi',

        'cawangan',
        'no_ahli',

        'alamat',

        'status_culaan',
        'kategori_culaan',
        'notes',

        'updated_by'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function culaan()
    {
        return $this->belongsTo(Culaan::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Status Labels
    |--------------------------------------------------------------------------
    */

    // public static $statusCulaanLabels = [
    //     'A' => 'Ahli Amanah',
    //     'C' => 'Condong PN',
    //     'D' => 'Dacing / Ahli & Penyokong BN',
    //     'E' => 'Empty / Tidak pasti',
    //     'O' => 'Belum Culaan',
    // ];
}