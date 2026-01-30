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
            $table->foreignId('dun_id')->constrained('dun');
            $table->string('koddm')->unique();
            ;
            $table->string('namadm');
            $table->timestamps();
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
