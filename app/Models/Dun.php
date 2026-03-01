<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; // Import the trait
use Spatie\Activitylog\LogOptions; // Import LogOptions

class Dun extends Model
{
    use LogsActivity; // Add the LogsActivity trait

    protected $table = 'dun';

    protected $fillable = [
        'parlimen_id',
        'kod_dun',       // Ensure 'kod_dun' is part of fillable fields
        'namadun',
        'status',
        'effective_from',
        'effective_to'
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    /**
     * Get the Parlimen that owns the Dun
     */
    public function parlimen()
    {
        return $this->belongsTo(Parlimen::class, 'parlimen_id');
    }

    /**
     * Get all Lokaliti through Dm
     */
public function lokalitis()
{
    return $this->hasManyThrough(
        Lokaliti::class,
        Dm::class,
        'kod_dun',      // Foreign key on dm table
        'koddm',       // Foreign key on lokaliti table
        'kod_dun',      // Local key on dun table
        'koddm'        // Local key on dm table
    );
}

    /**
     * Get all DMs under this Dun (updated to use 'kod_dun')
     */
 public function dms()
{
    return $this->hasMany(Dm::class, 'kod_dun', 'kod_dun');
}

    /**
     * Get all Pengundis through Dm
     */
    public function pengundis()
    {
        return $this->hasManyThrough(Pengundi::class, Dm::class);
    }

    /**
     * Get the activity log options.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('dun')  // Set log name to 'dun'
            ->logOnly(['kod_dun', 'namadun', 'parlimen_id']) // Only log changes to these attributes
            ->logOnlyDirty() // Log only dirty (changed) fields
            ->dontSubmitEmptyLogs() // Don't submit empty logs if no changes
            ->setDescriptionForEvent(fn(string $eventName) => "Dun with ID {$this->id} was {$eventName}"); // Custom log description
    }

    /**
     * Boot method to automatically log Dun actions
     */
    protected static function booted()
    {
        static::created(function ($dun) {
            activity()->performedOn($dun)->log("Dun with ID {$dun->id} created.");
        });

        static::updated(function ($dun) {
            activity()->performedOn($dun)->log("Dun with ID {$dun->id} updated.");
        });

        static::deleted(function ($dun) {
            activity()->performedOn($dun)->log("Dun with ID {$dun->id} deleted.");
        });
    }
}
