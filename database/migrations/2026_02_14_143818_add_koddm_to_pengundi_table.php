<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengundi', function (Blueprint $table) {
            $table->string('koddm')->nullable()->after('dm_id');
        });

        
    }

    public function down(): void
    {
        Schema::table('pengundi', function (Blueprint $table) {
            $table->dropColumn('koddm');
        });
    }
};
