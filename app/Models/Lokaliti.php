<?php
 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

 

class Lokaliti extends Model
{
    protected $table = 'lokaliti';

    protected $fillable = [
        'dm_id',
        'kod_lokaliti',
        'nama_lokaliti',
        'status',
        'effective_from',
        'effective_to',
    ];

    protected $dates = [
        'effective_from',
        'effective_to',
        'created_at',
        'updated_at',
    ];

    public function dm()
    {
        return $this->belongsTo(Dm::class);
    }

    public function dun()
    {
        return $this->hasOneThrough(
            Dun::class,
            Dm::class,
            'id',        // Foreign key on Dm table
            'id',        // Foreign key on Dun table
            'dm_id',     // Local key on Lokaliti
            'dun_id'     // Local key on Dm
        );
    }

    public function parlimen()
    {
        return $this->hasOneThrough(
            Parlimen::class,
            Dun::class,
            'id',
            'id',
            'dm_id',
            'parlimen_id'
        );
    }

    public function pengundis()
    {
        return $this->hasMany(Pengundi::class);
    }
}

