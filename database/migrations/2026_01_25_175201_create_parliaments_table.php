<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parliaments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // nama kawasan Parlimen
            $table->string('code')->nullable()->unique(); // optional, kod Parlimen
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parliaments');
    }
};
