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

            $table->string('kod_dm');

            $table->string('kod_lokaliti');
            $table->string('nama_lokaliti');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            $table->timestamps();

            $table->unique(
                [
                    'kod_lokaliti',
                    'kod_dm',
                    'nama_lokaliti',
                    'status',
                    'effective_from',
                    'effective_to'
                ],
                'unique_lokaliti_full'
            );
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
