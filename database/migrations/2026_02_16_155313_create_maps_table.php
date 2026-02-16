<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMapsTable extends Migration
{
// Migration file for maps table
public function up()
{
    Schema::create('maps', function (Blueprint $table) {
        $table->id();
        $table->string('type'); // 'parlimen' or 'dun'
        $table->string('code'); // The specific code (e.g., 'P.021 Kota Bharu', 'N.11 Tendong')
        $table->json('geojson'); // Store the GeoJSON data as a JSON object
            $table->timestamp('date'); // Date the data was fetched

        $table->timestamps();
    });
}


    public function down()
    {
        Schema::dropIfExists('maps');
    }
}
