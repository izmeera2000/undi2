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
        Schema::create('dm', function (Blueprint $table) {
            $table->id();

            $table->string('kod_dun');
            $table->string('kod_dm');
            $table->string('nama_dm');

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            $table->timestamps();

            // 🔥 THIS is the real fix
            $table->unique([
                'kod_dm',
                'kod_dun',
                'nama_dm',
                'effective_from',
                'effective_to'
            ], 'unique_dm_full');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dm');
    }
};
