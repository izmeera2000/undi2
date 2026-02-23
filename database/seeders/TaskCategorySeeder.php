<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaskCategory;

class TaskCategorySeeder extends Seeder
{
    public function run(): void
    {
        TaskCategory::insert([
            [
                'name' => 'Work',
                'description' => 'Work related tasks',
            ],
            [
                'name' => 'Personal',
                'description' => 'Personal tasks',
            ],
        ]);
    }
}