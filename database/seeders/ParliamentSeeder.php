<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
 use App\Models\Parliament;

class ParliamentSeeder extends Seeder
{
    public function run(): void
    {
        Parliament::create([
            'name' => 'KOTA BHARU',
            'code' => '022',
        ]);
    }
}

