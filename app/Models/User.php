<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Group;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use SoftDeletes;

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

    public function getRoleBadgeAttribute()
    {
        return match ($this->role) {
            'admin' => '<span class="badge bg-primary-light text-primary">Admin</span>',
            'moderator' => '<span class="badge bg-info-light text-info">Moderator</span>',
            'user' => '<span class="badge bg-secondary-light text-secondary">User</span>',
            default => '<span class="badge bg-light text-dark">Unknown</span>',
        };
    }

    public function groups()
    {
        return $this->belongsToMany(
            Group::class,
            'user_groups',   // pivot table
            'user_id',       // foreign key on pivot
            'group_id'         // related key on pivot
        )->withTimestamps();
    }



}
