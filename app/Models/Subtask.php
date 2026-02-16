<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Subtask extends Model
{
    use LogsActivity;

    protected $fillable = [
        'task_id',
        'title',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    // Define which fields to log
    protected static $logAttributes = ['title', 'is_completed', 'task_id'];

    // Define custom log name for tasks
    protected static $logName = 'task';

    /**
     * Get the activity log options.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('task') // Log name for task activity
            ->logOnly([ 
                'title',         // Log the 'title' attribute
                'is_completed',  // Log the 'is_completed' attribute
                'task_id',       // Log the 'task_id' attribute
            ])
            ->logOnlyDirty()         // Log only changed attributes
            ->dontSubmitEmptyLogs()  // Avoid submitting logs if no attributes are changed
            ->setDescriptionForEvent(fn(string $eventName) => "Updated subtask on task {$this->task->id}"); // Custom description
    }

    /*
    |----------------------------------------------------------------------
    | Relationships
    |----------------------------------------------------------------------
    */

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /*
    |----------------------------------------------------------------------
    | Boot Method (Auto Log Activity)
    |----------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::updated(function ($subtask) {
            // Log completion activity when 'is_completed' is updated
            if ($subtask->isDirty('is_completed') && $subtask->is_completed) {
                activity()
                    ->performedOn($subtask->task)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'subtask' => $subtask->title
                    ])
                    ->log("subtask completed on task {$subtask->task->id}");
            }
        });
    }
}
