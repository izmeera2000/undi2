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
        Schema::table('pengundi', function (Blueprint $table) {
            // Add election_id column with foreign key
            $table->foreignId('election_id')->nullable()->after('type_data_id')
                ->constrained('elections')
                ->nullOnDelete();
            if (Schema::hasColumn('pengundi', 'pilihan_raya_type')) {
                $table->dropColumn('pilihan_raya_type');
            }

                $table->dropUnique(['nokp_baru', 'tarikh_undian']);
    $table->unique(['nokp_baru', 'election_id']);


            if (Schema::hasColumn('pengundi', 'pilihan_raya_series')) {
                $table->dropColumn('pilihan_raya_series');
            }
                $table->dropColumn('tarikh_undian');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengundi', function (Blueprint $table) {
            $table->dropForeign(['election_id']);
            $table->dropColumn('election_id');

        });
    }
};