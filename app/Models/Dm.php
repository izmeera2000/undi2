<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; // Import the trait
use Spatie\Activitylog\LogOptions; // Import LogOptions

class Dm extends Model
{
    use LogsActivity; // Add the LogsActivity trait

    protected $table = 'dm';

    protected $fillable = [
        'kod_dun',        // Change dun_id to kod_dun
        'koddm',
        'namadm',
        'status',
        'effective_from',
        'effective_to'
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    /**
     * Get the Dun that owns the Dm
     */
    public function dun()
    {
        return $this->belongsTo(Dun::class, 'kod_dun', 'kod_dun');
    }

    /**
     * Get the related Parlimen via Dun
     */
    public function parlimen()
    {
        return $this->hasOneThrough(
            Parlimen::class,
            Dun::class,
            'id',            // Foreign key on the Dun model
            'id',            // Foreign key on the Parlimen model
            'kod_dun',       // Local key on the Dm model (updated to kod_dun)
            'parlimen_id'    // Local key on the Dun model
        );
    }

    /**
     * Get the Lokalitis that belong to this Dm
     */
    public function lokalitis()
    {
        return $this->hasMany(Lokaliti::class, 'koddm', 'koddm');
    }

    /**
     * Get the activity log options.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('dm')  // Set log name to 'dm'
            ->logOnly(['koddm', 'namadm', 'kod_dun']) // Log changes to kod_dun instead of dun_id
            ->logOnlyDirty() // Log only dirty (changed) fields
            ->dontSubmitEmptyLogs() // Don't submit empty logs if no changes
            ->setDescriptionForEvent(fn(string $eventName) => "Dm with ID {$this->id} was {$eventName}"); // Custom log description
    }

    /**
     * Boot method to automatically log Dm actions
     */
    protected static function booted()
    {
        static::created(function ($dm) {
            activity()->performedOn($dm)->log("Dm with ID {$dm->id} created.");
        });

        static::updated(function ($dm) {
            activity()->performedOn($dm)->log("Dm with ID {$dm->id} updated.");
        });

        static::deleted(function ($dm) {
            activity()->performedOn($dm)->log("Dm with ID {$dm->id} deleted.");
        });
    }
}
