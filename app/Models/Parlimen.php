<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; // Import the trait
use Spatie\Activitylog\LogOptions; // Import LogOptions

class Parlimen extends Model
{
    use LogsActivity; // Add the LogsActivity trait

    protected $table = 'parlimen';

    protected $fillable = ['kod_par', 'nama_par'];

    /**
     * Get all DUNs under this Parlimen
     */
    public function duns()
    {
        return $this->hasMany(Dun::class, 'kod_par', 'id');
    }

    /**
     * Get the activity log options.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('parlimen')  // Set log name to 'parlimen'
            ->logOnly(['kod_par', 'nama_par']) // Only log changes to 'kod_par' and 'nama_par'
            ->logOnlyDirty() // Only log dirty (changed) fields
            ->dontSubmitEmptyLogs() // Don't submit empty logs if no changes
            ->setDescriptionForEvent(fn(string $eventName) => "Parlimen with ID {$this->id} was {$eventName}"); // Custom log description
    }

    /**
     * Boot method to automatically log Parlimen actions
     */
    protected static function booted()
    {
        static::created(function ($parlimen) {
            activity()->performedOn($parlimen)->log("Parlimen with ID {$parlimen->id} created.");
        });

        static::updated(function ($parlimen) {
            activity()->performedOn($parlimen)->log("Parlimen with ID {$parlimen->id} updated.");
        });

        static::deleted(function ($parlimen) {
            activity()->performedOn($parlimen)->log("Parlimen with ID {$parlimen->id} deleted.");
        });
    }
}
