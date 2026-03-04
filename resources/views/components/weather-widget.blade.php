<?php

use Livewire\Component;
use App\Models\WeatherForecast;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

new class extends Component {
    public $weather = [];
    public $loadFailed = false;

    public function mount()
    {
        $this->fetchWeather();
    }


    public function fetchWeather()
    {
        try {
            $today = now()->toDateString();
            $location = 'Pasir Mas';

            // First, try fetching from DB
            $weather = WeatherForecast::whereDate('forecast_date', $today)
                ->whereRaw('LOWER(location_name) = ?', [strtolower($location)])
                ->first();

            // If not in DB, call external API
            if (!$weather) {
                $response = Http::get("https://api.data.gov.my/weather/forecast/?contains=" . urlencode($location) . "@location__location_name");


                $json = $response->json();

                if ($response->successful() && !empty($json)) {
                    $data = $json[0] ?? null;

                    if ($data && isset($data['location']['location_name'])) {
                        $weather = WeatherForecast::updateOrCreate(
                            [
                                'forecast_date' => $today,
                                'location_name' => $data['location']['location_name']
                            ],
                            [
                                'max_temp' => $data['max_temp'] ?? null,
                                'min_temp' => $data['min_temp'] ?? null,
                                'summary_forecast' => $data['summary_forecast'] ?? null
                            ]
                        );
                    }
                }
            }

            // Set Livewire property
            $this->weather = $weather ? $weather->toArray() : [
                'location_name' => $location,
                'max_temp' => '--',
                'min_temp' => '--',
                'forecast_date' => $today,
            ];

            $this->loadFailed = false;

        } catch (\Throwable $e) {
            logger()->error("Weather Widget Failed: " . $e->getMessage());
            $this->weather = [];
            $this->loadFailed = true;
        }
    }
};
?>

<div class="weather-widget-container">
    @placeholder
    <div class="card widget-weather-image-card shimmer-card border-0">
        <div class="widget-weather-image-bg" style="background: #e2e8f0; height: 155px;">
            <div class="widget-weather-image-content">
                <div class="skeleton-text skeleton-location"></div>
                <div class="skeleton-text skeleton-temp"></div>
                <div class="skeleton-text skeleton-day"></div>
            </div>
        </div>
        <style>
            .shimmer-card {
                overflow: hidden;
            }

            .skeleton-text {
                background: linear-gradient(90deg, #cbd5e1 25%, #f1f5f9 50%, #cbd5e1 75%);
                background-size: 200% 100%;
                animation: weather-shimmer 1.5s infinite;
                border-radius: 4px;
                margin-bottom: 10px;
            }

            .skeleton-location {
                width: 120px;
                height: 18px;
            }

            .skeleton-temp {
                width: 180px;
                height: 45px;
                margin: 15px 0;
            }

            .skeleton-day {
                width: 80px;
                height: 16px;
            }

            @keyframes weather-shimmer {
                0% {
                    background-position: 200% 0;
                }

                100% {
                    background-position: -200% 0;
                }
            }
        </style>
    </div>
    @endplaceholder

    {{-- Main Content --}}
    @if($loadFailed)
        <div class="card widget-weather-image-card border-0">
            <div class="widget-weather-image-bg" style="background: #6c757d; opacity: 0.8; height: 155px;">
                <div class="widget-weather-image-content text-center">
                    <i class="bi bi-cloud-slash mb-2 text-white" style="font-size: 2rem;"></i>
                    <div class="small text-white text-opacity-75">Weather service currently unavailable</div>
                    <button wire:click="fetchWeather" class="btn btn-sm btn-light mt-2 py-0" style="font-size: 0.7rem;">
                        Try Again
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="card widget-weather-image-card border-0">
            <div class="widget-weather-image-bg"
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 155px;">
                <div class="widget-weather-image-content">
                    <div class="widget-weather-location">
                        {{ $weather['location_name'] ?? 'Pasir Mas' }}
                    </div>

                    <div class="widget-weather-temp-large">
                        <i class="bi bi-cloud-sun"></i>
                        <span>
                            {{ $weather['max_temp'] ?? '--' }}°<small>C / {{ $weather['min_temp'] ?? '--' }}°C</small>
                        </span>
                    </div>

                    <div class="widget-weather-day">
                        {{ isset($weather['forecast_date']) ? Carbon::parse($weather['forecast_date'])->format('l') : now()->format('l') }}
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>