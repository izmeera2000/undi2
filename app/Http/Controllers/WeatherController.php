<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\WeatherForecast;

class WeatherController extends Controller
{
    /**
     * Return today's weather forecast as JSON
     */
    public function today($location = 'Pasir Mas')
    {
        $today = now()->toDateString();

        // Check DB first
        $weather = WeatherForecast::where('forecast_date', $today)
            ->where('location_name', $location)
            ->first();

        if (!$weather) {
            $response = Http::get("https://api.data.gov.my/weather/forecast?contains={$location}@location__location_name");

            if ($response->successful() && !empty($response->json())) {
                $data = $response->json()[0];

                $weather = WeatherForecast::updateOrCreate(
                    [
                        'forecast_date' => $today,
                        'location_name' => $data['location']['location_name'],
                    ],
                    [
                        'max_temp' => $data['max_temp'],
                        'min_temp' => $data['min_temp'],
                        'summary_forecast' => $data['summary_forecast'] ?? null,
                    ]
                );
            }
        }

        return response()->json($weather);
    }
}
