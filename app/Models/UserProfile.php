<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; // Import the trait
use Spatie\Activitylog\LogOptions; // Import LogOptions

class UserProfile extends Model
{
    use LogsActivity; // Use the LogsActivity trait

    protected $fillable = [
        'user_id',
        'phone',
        'bio',
        'address',
        'profile_picture',
        'cover_picture',
    ];

    // Get the user associated with the profile
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Get the URL of the profile picture
    public function getProfilePictureUrlAttribute()
    {
        return $this->profile_picture
            ? asset('storage/' . $this->profile_picture)
            : asset('assets/img/avatars/avatar-placeholder.webp');
    }

    // Activity logging options
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('user')  // Log name for the user profile
            ->logOnly(['phone', 'bio', 'address', 'profile_picture', 'cover_picture']) // Log only these attributes
            ->logOnlyDirty() // Log only changed (dirty) fields
            ->setDescriptionForEvent(fn(string $eventName) => "User {$this->user->name} was {$eventName}."); // Custom description for the event
    }

    // Model Events - Automatically log profile actions (create, update, delete)
    protected static function booted()
    {
 

        static::updated(function ($profile) {
            // \Log::info('User Updated: ' . $profile->user->name); // Debugging line
            activity()->performedOn($profile)->log("User   {$profile->user->name} updated.");
        });

        static::deleted(function ($profile) {
            // \Log::info('User Deleted: ' . $profile->user->name); // Debugging line
            activity()->performedOn($profile)->log("User   {$profile->user->name} deleted.");
        });
    }
}
