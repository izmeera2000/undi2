<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\TaskCategory;
use App\Models\Subtask;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Task extends Model
{
    use LogsActivity;

    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */

  public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('task') // Log name for task activity
            ->logOnly([ 
                'title',         // Log the 'title' attribute
                'status',        // Log the 'status' attribute
                'priority',      // Log the 'priority' attribute
                'due_at',        // Log the 'due_at' attribute
                'assigned_to',   // Log the 'assigned_to' attribute
            ])
            ->logOnlyDirty()         // Log only changed attributes
            ->dontSubmitEmptyLogs()  // Avoid submitting logs if no attributes are changed
            ->setDescriptionForEvent(fn(string $eventName) => "Updated task {$this->id}"); // Custom description
    }

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_at',
        'created_by',
        'assigned_to',
        'category_id',
        'tags',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casting
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'tags'   => 'array',
        'due_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function category()
    {
        return $this->belongsTo(TaskCategory::class, 'category_id');
    }

  
    public function subtasks()
    {
        return $this->hasMany(Subtask::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeCompleted($query)
    {
        return $query->where('status', 'done');
    }

    public function scopePending($query)
    {
        return $query->where('status', '!=', 'done');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_at', '<', now())
                     ->where('status', '!=', 'done');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper
    |--------------------------------------------------------------------------
    */

    public function progressPercentage()
    {
        $total = $this->subtasks()->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->subtasks()
                          ->where('is_completed', true)
                          ->count();

        return round(($completed / $total) * 100);
    }
}
