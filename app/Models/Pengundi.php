<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengundi extends Model
{
    use HasFactory;

    protected $table = 'pengundi';

    protected $fillable = [
        'locality_id',
        'bangsa',
        'jantina',
        'kategori',
        'umur',
        'status',
        'date_vote',
        'cula',
        'status_cula',
        'added_by',
    ];

    /**
     * Relasi ke Locality
     */
    public function locality()
    {
        return $this->belongsTo(Locality::class);
    }

    protected $appends = ['full_code'];  


    public function getFullCodeAttribute()
    {
        $parliament = optional($this->locality->dm->dun->parliament)->code ?? '000';
        $dun = optional($this->locality->dm->dun)->code ?? '00';
        $dm = optional($this->locality->dm)->code ?? '00';
        $locality = optional($this->locality)->code ?? '00';
 
        return $parliament . $dun . $dm . $locality  ;
    }



    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
