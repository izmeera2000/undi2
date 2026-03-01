<?php

use Livewire\Component;
use App\Http\Controllers\WeatherController;
use Carbon\Carbon;

new class extends Component {
    public $weather = [];

    public function mount()
    {
        $this->fetchWeather();
    }

    public function fetchWeather()
    {
        $controller = app(WeatherController::class);
        $response = $controller->today('Pasir Mas');

        $this->weather = method_exists($response, 'getData')
            ? (array) $response->getData(true)
            : $response;
    }
}; 
?>

@placeholder
<div class="card widget-weather-image-card shimmer-card">
    <div class="widget-weather-image-bg" style="background: #e2e8f0; height: 155px;">
        <div class="widget-weather-image-content">
            <div class="skeleton-text skeleton-location"></div>
            <div class="skeleton-text skeleton-temp"></div>
            <div class="skeleton-text skeleton-day"></div>
        </div>
    </div>
    <style>
        .shimmer-card { overflow: hidden; border: none; }
        .skeleton-text {
            background: linear-gradient(90deg, #cbd5e1 25%, #f1f5f9 50%, #cbd5e1 75%);
            background-size: 200% 100%;
            animation: weather-shimmer 1.5s infinite;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .skeleton-location { width: 120px; height: 18px; }
        .skeleton-temp { width: 180px; height: 45px; margin: 15px 0; }
        .skeleton-day { width: 80px; height: 16px; }
        @keyframes weather-shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</div>
@endplaceholder

<div class="card widget-weather-image-card border-0">
    <div class="widget-weather-image-bg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="widget-weather-image-content">
            <div class="widget-weather-location">
                {{ $weather['location_name'] ?? 'Data Missing' }}
            </div>

            <div class="widget-weather-temp-large">
                <i class="bi bi-cloud-sun"></i>
                <span>
                    {{ $weather['max_temp'] ?? '--' }}°<small>C / {{ $weather['min_temp'] ?? '--' }}°C</small>
                </span>
            </div>

            <div class="widget-weather-day">
                {{ isset($weather['forecast_date']) ? Carbon::parse($weather['forecast_date'])->format('l') : '---' }}
            </div>
        </div>
    </div>
</div>
