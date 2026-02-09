<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'bio',
        'address',
        'profile_picture',
        'cover_picture',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture
            ? asset('storage/' . $this->profile_picture)
            : asset('assets/img/avatars/avatar-placeholder.webp');
    }

}
