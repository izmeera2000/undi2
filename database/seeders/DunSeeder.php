<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Parliament;
use App\Models\DUN;
class DunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parliament = Parliament::first();

        DUN::create([
            'name' => 'TENDONG',
            'code' => '11',
            'parliament_id' => $parliament->id,
        ]);
    }
}
