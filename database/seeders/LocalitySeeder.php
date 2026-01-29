<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Locality;
use App\Models\DM;

class LocalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        $dm = DM::first();

        Locality::create([
            'name'  => 'KG TENDONG CHINA',
            'code'  => '12',
            
            'dm_id' => $dm->id,
        ]);
    }
}
