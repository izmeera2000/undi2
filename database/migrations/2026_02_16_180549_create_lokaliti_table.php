<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lokaliti', function (Blueprint $table) {
            $table->id();

            $table->string('koddm')->nullable();

            $table->string('kod_lokaliti')->nullable();
            $table->string('nama_lokaliti');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();

            $table->timestamps();

            $table->index('nama_lokaliti');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokaliti');
    }
};
