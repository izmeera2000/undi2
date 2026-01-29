<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('localities', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->unique()->nullable();
             $table->foreignId('dm_id')->nullable()->constrained('dms')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('localities');
  
        }
 
};
