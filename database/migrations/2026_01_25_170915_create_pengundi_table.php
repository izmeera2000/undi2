<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengundi', function (Blueprint $table) {
            $table->id();


            $table->foreignId('locality_id')->nullable()->constrained('localities')->nullOnDelete();

            $table->string('bangsa');
            $table->enum('jantina', ['Lelaki', 'Perempuan']);
            $table->string('kategori');
            $table->unsignedInteger('umur');

            $table->string('status');
            $table->date('date_vote')->nullable();

            $table->boolean('cula')->default(false);
            $table->string('status_cula')->nullable();

            $table->unsignedBigInteger('added_by');

            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('pengundi');
    }
};
