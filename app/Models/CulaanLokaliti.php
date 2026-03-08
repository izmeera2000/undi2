<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CulaanLokaliti extends Model
{
    protected $table = 'culaan_lokaliti';

    protected $fillable = [
        'culaan_id',
        'lokaliti_id',
        'total_voters',
        'strong_support',
        'likely_support',
        'fence_sitters',
        'opponent_support',
        'unknown_voters',
        'expected_turnout',
        'postal_votes',
        'early_votes',
        'youth_voters',
        'notes',
        'updated_by'
    ];

    protected $casts = [
        'expected_turnout' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function culaan()
    {
        return $this->belongsTo(Culaan::class);
    }

    public function lokaliti()
    {
        return $this->belongsTo(Lokaliti::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function estimatedVotes()
{
    return
        ($this->strong_support ?? 0) * 0.9 +
        ($this->likely_support ?? 0) * 0.7 +
        ($this->fence_sitters ?? 0) * 0.4;
}
}