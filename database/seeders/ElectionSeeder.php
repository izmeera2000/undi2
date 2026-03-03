<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Election;

class ElectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        Election::insert([
            ['type' => 'PRU', 'number' => '12', 'year' => 2008],
            ['type' => 'PRU', 'number' => '15', 'year' => 2022],
        ]);
    }
}
