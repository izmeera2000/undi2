<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('members', function (Blueprint $table) {
        $table->dropForeign(['dun_id']); // drop old FK
        $table->renameColumn('dun_id', 'kod_dun');
        $table->foreign('kod_dun')
            ->references('kod_dun')
            ->on('duns')
            ->cascadeOnDelete();
    });
}

public function down(): void
{
    Schema::table('members', function (Blueprint $table) {
        $table->dropForeign(['kod_dun']);
        $table->renameColumn('kod_dun', 'dun_id');
        $table->foreign('dun_id')
            ->references('id')
            ->on('duns')
            ->cascadeOnDelete();
    });
}
};
