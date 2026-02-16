<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'code', 'geojson', 'date'];

    protected $casts = [
        'geojson' => 'array',  // Ensure geojson is treated as an array
    ];
}
