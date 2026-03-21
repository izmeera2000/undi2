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

            $table->string('kod_par');
            $table->foreign('kod_par')
                ->references('kod_par')
                ->on('parlimen')
                ->cascadeOnDelete();

            $table->string('kod_dun');
            $table->string('nama_dun');

            // Restructure control
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            $table->timestamps();

            $table->unique([
                'kod_dun',
                'kod_par',
                'nama_dun',
                'effective_from',
                'effective_to'
            ], 'unique_dun_full');
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
