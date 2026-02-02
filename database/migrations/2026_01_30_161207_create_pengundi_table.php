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
        Schema::create('pengundi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dm_id')->constrained('dm');

            $table->string('nokp_baru');
            $table->string('nokp_lama')->nullable();
            $table->string('nama');
            $table->string('jantina')->nullable();
            $table->string('bangsa')->nullable();
            $table->integer('umur')->nullable();
            $table->integer('tahun_lahir')->nullable();
            $table->text('alamat_spr')->nullable();
            $table->text('alamat_jpn_1')->nullable();
            $table->text('alamat_jpn_2')->nullable();
            $table->text('alamat_jpn_3')->nullable();
            $table->text('poskod')->nullable();
            $table->text('bandar')->nullable();
            $table->text('negeri')->nullable();
            $table->string('status_umno')->nullable();
            $table->string('status_baru')->nullable();
            $table->year('tarikh_undian'); // 👈 YEAR type

            // ✅ COMPOSITE UNIQUE
            $table->unique(['nokp_baru', 'tarikh_undian']);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengundi');
    }
};
