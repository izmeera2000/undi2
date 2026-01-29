<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Locality;
use App\Models\Pengundi;
class PengundiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
      public function run(): void
    {
        $locality = Locality::first();

        Pengundi::create([
            'locality_id' => $locality->id,
            'bangsa'      => 'melayu',
            'jantina'     => 'Lelaki',
            'kategori'     => 1,
            'umur'        => 19,
            'status'      => 1,
            'added_by'      => 1,
        ]);
    }
}
