<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CulaanPengundi;

class CulaanPengundiSeeder extends Seeder
{
    public function run(): void
    {
        CulaanPengundi::factory()->count(100)->create();
    }
}