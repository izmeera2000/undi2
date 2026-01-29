<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DM;
use App\Models\DUN;
class DmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
       public function run(): void
    {
        $dun = DUN::first();

        DM::create([
            'name'   => 'TENDONG',
            'code'   => '12',
            'dun_id' => $dun->id,
        ]);
    }
}
