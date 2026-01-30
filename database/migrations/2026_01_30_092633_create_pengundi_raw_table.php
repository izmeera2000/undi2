<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengundi_raw', function (Blueprint $table) {
            $table->id();

            $table->string('kod_par')->nullable();
            $table->string('namapar')->nullable();

            $table->string('kod_dun')->nullable();
            $table->string('namadun')->nullable();

            $table->string('koddm')->nullable();
            $table->string('namadm')->nullable();

            $table->string('kodlokaliti')->nullable();
            $table->string('namalokaliti')->nullable();

            $table->string('nokp_baru')->nullable();
            $table->string('nokp_lama')->nullable();

            $table->string('nama')->nullable();
            $table->text('alamat_spr')->nullable();

            $table->string('bangsa')->nullable();
            $table->string('bangsa_spr')->nullable();

            $table->string('jantina')->nullable();
            $table->string('status_baru')->nullable();

            $table->string('kodpar_pru12')->nullable();

            $table->integer('tahun_lahir')->nullable();
            $table->integer('umur')->nullable();

            $table->string('status_umno')->nullable();

            $table->string('alamat_jpn_1')->nullable();
            $table->string('alamat_jpn_2')->nullable();
            $table->string('alamat_jpn_3')->nullable();

            $table->string('poskod')->nullable();
            $table->string('bandar')->nullable();
            $table->string('negeri')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengundi_raw');
    }
};
