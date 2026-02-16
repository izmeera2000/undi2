<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherForecast extends Model
{
    use HasFactory;

    // Allow mass assignment on these columns
    protected $fillable = [
        'location_name',
        'forecast_date',
        'max_temp',
        'min_temp',
        'summary_forecast',
    ];
}
