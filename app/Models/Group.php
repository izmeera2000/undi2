<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Users that belong to this group
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_groups',
            'group_id',
            'user_id'
        )->withTimestamps();
    }

    public function members()
    {
        return $this->belongsToMany(
            Member::class,
            'member_groups',
            'group_id',
            'member_id'
        )->withTimestamps();
    }


}
