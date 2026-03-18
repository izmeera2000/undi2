<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lokaliti extends Model
{
    protected $table = 'lokaliti';

    protected $fillable = [
        'kod_dm',        // Make sure 'kod_dm' is correctly referenced here
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

    /**
     * The DM (District Manager) relationship.
     *
     * Update the foreign key to 'kod_dm' if it's changed from 'dm_id'.
     */
// In Lokaliti.php
public function dm()
{
    // Lokaliti.kod_dm → Dm.kod_dm
    return $this->belongsTo(Dm::class, 'kod_dm', 'kod_dm');
}

public function dun()
{
    // Get Dun via Dm: Dm.kod_dun → Dun.kod_dun
    return $this->hasOneThrough(
        Dun::class,     // Related model
        Dm::class,      // Intermediate model
        'kod_dm',        // Foreign key on Dm model (links to Lokaliti.kod_dm)
        'kod_dun',      // Foreign key on Dun model (links to Dm.kod_dun)
        'kod_dm',        // Local key on Lokaliti
        'kod_dun'       // Local key on Dm
    );
}

public function parlimen()
{
    // Get Parlimen via Dun: Dun.kod_parlimen → Parlimen.kod_parlimen
    return $this->hasOneThrough(
        Parlimen::class, // Related model
        Dun::class,      // Intermediate model
        'kod_dun',       // Foreign key on Dun (links to Lokaliti via dun relation)
        'kod_par',  // Foreign key on Parlimen
        'kod_dm',         // Local key on Lokaliti
        'kod_par'   // Local key on Dun
    );
}

    /**
     * Get the associated Pengundis for this Lokaliti.
     */
    public function pengundis()
    {
        return $this->hasMany(Pengundi::class);
    }
}
