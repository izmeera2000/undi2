<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => Hash::make('test'),
            'role' =>'admin',
        ]);

        $testUser->profile()->create([
            'phone' => '123-456-7890',
            'bio' => 'This is a test user bio.',
            'address' => '123 Test Street',
            'profile_picture' => null,
            'cover_picture' => null,
        ]);

        $testUser->assignRole('admin');



        $testUser2 = User::factory()->create([
            'name' => 'Test2 Admin',
            'email' => 'test2@example.com',
            'password' => Hash::make('test'),
            'role' =>'user',

        ]);

        $testUser2->profile()->create([
            'phone' => '123-456-7892',
            'bio' => 'This is a test user bio2.',
            'address' => '123 Test Street2',
            'profile_picture' => null,
            'cover_picture' => null,
        ]);

        $testUser2->assignRole('user');
    }
}