<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CulaanPengundiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'culaan_id' => 2,

            'kod_lokaliti' => $this->faker->randomElement(['0221112002', '0221112003', '0221112004', '0221112005']),

            'lokaliti' => $this->faker->name,
            'pm' => $this->faker->name,

            'no_siri' => $this->faker->numberBetween(1, 500),
            'saluran' => $this->faker->randomElement(['1', '2', '3', '4']),

            'nama' => $this->faker->name,
            'no_kp' => $this->faker->numerify('############'),

            'jantina' => $this->faker->randomElement(['L', 'P']),
            'umur' => $this->faker->numberBetween(18, 90),
            'bangsa' => $this->faker->randomElement(['Melayu', 'Cina', 'India', 'Lain-lain']),

            'kategori_pengundi' => $this->faker->randomElement(['', 'Pengundi Luar']),
            'status_pengundi' => $this->faker->randomElement(['OKU', 'Penerima Bantuan']),
            'status_ahli' => $this->faker->randomElement(['', 'Biasa']),
            'kategori_ahli' => $this->faker->randomElement(['Biasa', 'PUTERI']),

            'cawangan' => $this->faker->word,
            'no_ahli' => $this->faker->numerify('AHLI####'),

            'alamat' => $this->faker->address,

            'status_culaan' => $this->faker->randomElement(['A', 'C', 'D', 'E', 'O']),
             'notes' => $this->faker->sentence,

            'updated_by' => 1, // assume user id 1 exists
        ];
    }
}