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
        Schema::create('culaan_pengundis', function (Blueprint $table) {

            $table->id();

            $table->foreignId('culaan_id')->constrained()->cascadeOnDelete();


            // Snapshot voter data (for culaan)
            $table->string('kod_lokaliti')->nullable();
            $table->string('lokaliti')->nullable();
            $table->string('pm')->nullable();

            $table->integer('no_siri')->nullable();
            $table->string('saluran')->nullable();

            $table->string('nama')->nullable();
            $table->string('no_kp', 12)->nullable();

            $table->string('jantina')->nullable();
            $table->integer('umur')->nullable();
            $table->string('bangsa')->nullable();

            $table->string('kategori_pengundi')->nullable();
            $table->string('status_pengundi')->nullable();
            $table->string('status_culaan')->nullable();

            $table->string('cawangan')->nullable();
            $table->string('no_ahli')->nullable();

            $table->text('alamat')->nullable();

            // Culaan fields
            $table->string('status_ahli')->nullable();
            $table->string('kategori_ahli')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('updated_by')->nullable()->constrained('users');

            $table->timestamps();

            $table->index(['culaan_id', 'status_culaan', 'no_kp']);

            $table->unique(['culaan_id', 'no_kp', 'kod_lokaliti']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('culaan_pengundis');
    }
};
