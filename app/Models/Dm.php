<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; // Import the trait
use Spatie\Activitylog\LogOptions; // Import LogOptions

class Dm extends Model
{
    use LogsActivity; // Add the LogsActivity trait

    protected $table = 'dm';

    protected $fillable = ['koddm', 'namadm', 'dun_id'];

    /**
     * Get the Dun that owns the Dm
     */
    public function dun()
    {
        return $this->belongsTo(Dun::class, 'dun_id');
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
            ->logOnly(['koddm', 'namadm', 'dun_id']) // Only log changes to these attributes
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
