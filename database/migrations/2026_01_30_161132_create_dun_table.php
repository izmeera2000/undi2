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


        Schema::create('dun', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parlimen_id')
                ->constrained('parlimen')
                ->cascadeOnDelete();

            $table->string('kod_dun');
            $table->string('nama_dun');

            // Restructure control
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();

            $table->timestamps();

            $table->index(['kod_dun', 'status']);
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
