<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; // Import the trait
use Spatie\Activitylog\LogOptions; // Import LogOptions

class Event extends Model
{
    use HasFactory;
    use LogsActivity; // Add the LogsActivity trait

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'all_day',
        'color',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
        'all_day'    => 'boolean',
    ];

    // Creator
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Participants
    public function participants()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the activity log options.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('event')  // Set log name to 'event'
            ->logOnly(['title', 'description', 'start_date', 'end_date', 'all_day', 'color']) // Log these attributes only
            ->logOnlyDirty() // Log only dirty (changed) fields
            ->dontSubmitEmptyLogs() // Don't submit empty logs if no changes
            ->setDescriptionForEvent(fn(string $eventName) => "Event with ID {$this->id} was {$eventName}"); // Custom log description
    }

    /**
     * Boot method to automatically log Event actions
     */
    protected static function booted()
    {
        static::created(function ($event) {
            activity()->performedOn($event)->log("Event with ID {$event->id} created.");
        });

        static::updated(function ($event) {
            activity()->performedOn($event)->log("Event with ID {$event->id} updated.");
        });

        static::deleted(function ($event) {
            activity()->performedOn($event)->log("Event with ID {$event->id} deleted.");
        });
    }
}
