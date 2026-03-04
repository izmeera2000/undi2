<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    protected $fillable = [
        'type',
        'number',
        'year',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')
            ->withDefault(function ($user, $election) {
                $user->name = 'System';
            });
    }
    
    public function getLabelAttribute()
    {
        return "{$this->type}-{$this->number} ({$this->year})";
    }
}