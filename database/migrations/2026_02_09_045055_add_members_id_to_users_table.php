<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'members_id')) {
                $table->foreignId('members_id')
                      ->nullable()
 
                      ->constrained('members')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'members_id')) {
                $table->dropForeign(['members_id']);
                $table->dropColumn('members_id');
            }
        });
    }
};
