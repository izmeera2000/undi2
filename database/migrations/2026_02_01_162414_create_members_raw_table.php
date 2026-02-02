<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('members_raw', function (Blueprint $table) {
            $table->id();

            $table->string('kod_bhgn')->nullable();
            $table->string('nama_bhgn')->nullable();

            $table->string('kod_dun')->nullable();
            $table->string('nama_dun')->nullable();

            $table->string('kod_cwgn')->nullable();
            $table->string('nama_cwgn')->nullable();

            $table->string('no_ahli')->nullable();

            $table->string('nokp_baru')->nullable()->index();
            $table->string('nokp_lama')->nullable();

            $table->string('nama')->nullable();

            $table->integer('tahun_lahir')->nullable();
            $table->integer('umur')->nullable();

            $table->string('jantina')->nullable();

            $table->text('alamat_1')->nullable();
            $table->text('alamat_2')->nullable();
            $table->text('alamat_3')->nullable();

            $table->string('bangsa')->nullable();

            $table->string('kod_dm')->nullable();

            $table->text('alamat_jpn_1')->nullable();
            $table->text('alamat_jpn_2')->nullable();
            $table->text('alamat_jpn_3')->nullable();

            $table->string('poskod')->nullable();
            $table->string('bandar')->nullable();
            $table->string('negeri')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members_raw');
    }
};
