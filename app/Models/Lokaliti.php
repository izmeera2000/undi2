<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lokaliti extends Model
{
    protected $table = 'lokaliti';

    protected $fillable = [
        'koddm',        // Make sure 'koddm' is correctly referenced here
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
     * Update the foreign key to 'koddm' if it's changed from 'dm_id'.
     */
    public function dm()
    {
        return $this->belongsTo(Dm::class, 'koddm', 'koddm');  // Ensure 'koddm' is used as foreign key
    }

    /**
     * Get the related Dun via Dm.
     */
    public function dun()
    {
        return $this->hasOneThrough(
            Dun::class,       // Related model
            Dm::class,        // Intermediate model
            'koddm',          // Foreign key on Dm model (linking with Lokaliti)
            'id',              // Foreign key on Dun model
            'dm_id',           // Local key on Lokaliti model (dm_id if this is correct)
            'dun_id'           // Local key on Dm model (dun_id)
        );
    }

    /**
     * Get the related Parlimen via Dun.
     */
    public function parlimen()
    {
        return $this->hasOneThrough(
            Parlimen::class,  // Related model
            Dun::class,       // Intermediate model
            'dm_id',          // Foreign key on Dun model
            'id',             // Foreign key on Parlimen model
            'koddm',          // Local key on Lokaliti model (should match the correct key)
            'parlimen_id'     // Local key on Dun model
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
