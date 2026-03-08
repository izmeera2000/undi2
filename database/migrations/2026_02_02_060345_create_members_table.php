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
        Schema::create('members', function (Blueprint $table) {
            $table->id();


            $table->string('kod_cwgn')->nullable();
            $table->string('nama_cwgn')->nullable();
            $table->string('no_ahli')->nullable();

            $table->string('nokp_baru')->unique();
            $table->string('nokp_lama')->nullable();

            $table->string('nama');
            $table->integer('tahun_lahir')->nullable();
            $table->integer('umur')->nullable();
            $table->string('jantina')->nullable();

            $table->string('alamat_1')->nullable();
            $table->string('alamat_2')->nullable();
            $table->string('alamat_3')->nullable();

            $table->string('bangsa')->nullable();
            $table->string('kod_dm')->nullable();

            $table->string('alamat_jpn_1')->nullable();
            $table->string('alamat_jpn_2')->nullable();
            $table->string('alamat_jpn_3')->nullable();

            $table->string('poskod', 10)->nullable();
            $table->string('bandar')->nullable();
            $table->string('negeri')->nullable();

            $table->string('status_ahli')->nullable();
 
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
