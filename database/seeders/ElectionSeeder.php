<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Election;
use Carbon\Carbon;

class ElectionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        Election::insert([
            [
                'type' => 'PRU',
                'number' => '12',
                'year' => 2008,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'type' => 'PRU',
                'number' => '15',
                'year' => 2022,
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}