<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add 'name' column if it doesn't exist
            if (!Schema::hasColumn('users', 'name')) {
                $table->string('name')->after('id')->nullable();
            }


        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            if (Schema::hasColumn('users', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
