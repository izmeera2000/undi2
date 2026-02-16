<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Group;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity; // Import the trait
use Spatie\Activitylog\LogOptions; // Import LogOptions

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use SoftDeletes;
    use HasRoles;
    use LogsActivity; // Import LogsActivity trait

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'name',
        'email',
        'profile_picture',
        'password',
        'role',
        'members_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // -------------------------------
    // Role / Group helper methods
    // -------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    // Events created by user
    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    // Events user participates in
    public function events()
    {
        return $this->belongsToMany(Event::class);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isSuspended()
    {
        return $this->status === 'suspended';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    // Status badge
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'active' => '<span class="badge bg-success">Active</span>',
            'suspended' => '<span class="badge bg-danger">Suspended</span>',
            'inactive' => '<span class="badge bg-secondary">Inactive</span>',
            'pending' => '<span class="badge bg-warning text-dark">First Login</span>',
            default => '<span class="badge bg-light text-dark">Unknown</span>',
        };
    }

    // Role badge
    public function getRoleBadgeAttribute()
    {
        return match ($this->role) {
            'admin' => '<span class="badge bg-primary-light text-primary">Admin</span>',
            'moderator' => '<span class="badge bg-info-light text-info">Moderator</span>',
            'user' => '<span class="badge bg-secondary-light text-secondary">User</span>',
            default => '<span class="badge bg-light text-dark">Unknown</span>',
        };
    }

    // Groups associated with the user
    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            'user_groups',   // pivot table
            'user_id',       // foreign key on pivot
            'group_id'       // related key on pivot
        )->withTimestamps();
    }

    // Activity logging options
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('user')  // Set the log name to 'user'
            ->logOnly(['name', 'email', 'role', 'status']) // Log only specific attributes
            ->logOnlyDirty() // Only log dirty (changed) attributes
            ->setDescriptionForEvent(fn(string $eventName) => "User {$this->name} was {$eventName}."); // Custom log description
    }

    // Model Events - Automatically log user actions (create, update, delete)
    protected static function booted()
    {
        static::created(function ($user) {
            // \Log::info('User Created: ' . $user->name); // Debugging line
            activity()->performedOn($user)->log("User {$user->name} created.");
        });

        static::updated(function ($user) {
            // \Log::info('User Updated: ' . $user->name); // Debugging line
            activity()->performedOn($user)->log("User {$user->name} updated.");
        });

        static::deleted(function ($user) {
            // \Log::info('User Deleted: ' . $user->name); // Debugging line
            activity()->performedOn($user)->log("User {$user->name} deleted.");
        });
    }
}
