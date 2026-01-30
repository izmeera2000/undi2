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
        Schema::create('dun', function (Blueprint $table) {
            $table->id();
                $table->foreignId('parlimen_id')->constrained('parlimen');
    $table->string('kod_dun')->unique();;
    $table->string('namadun');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dun');
    }
};
