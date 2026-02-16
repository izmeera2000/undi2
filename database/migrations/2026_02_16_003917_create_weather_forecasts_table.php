<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('weather_forecasts', function (Blueprint $table) {
        $table->id();
        $table->string('location_name');
        $table->date('forecast_date');
        $table->integer('max_temp');
        $table->integer('min_temp');
        $table->text('summary_forecast')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_forecasts');
    }
};
