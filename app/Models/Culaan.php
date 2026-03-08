<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; // Import the trait
use Spatie\Activitylog\LogOptions; // Import LogOptions


class Culaan extends Model
{

    protected $table = 'culaans';

    use LogsActivity;

    protected $fillable = [
        'election_id',
        'name',
        'date',
        'description',
        'created_by'
    ];

    public function election()
    {
        return $this->belongsTo(Election::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lokaliti()
    {
        return $this->hasMany(CulaanLokaliti::class);
    }

    public function lokalitiData()
    {
        return $this->hasMany(CulaanLokaliti::class);
    }

public function pengundis()
{
    return $this->hasMany(CulaanPengundi::class);
}

       public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('culaan')
            ->logOnlyDirty() // Log only dirty (changed) fields
            ->dontSubmitEmptyLogs() // Don't submit empty logs if no changes
            ->setDescriptionForEvent(fn(string $eventName) => "Dm with ID {$this->id} was {$eventName}"); // Custom log description
    }
}