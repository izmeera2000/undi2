<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SqlImportSeeder extends Seeder
{
    public function run(): void
    {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::disableQueryLog(); // 🔥 prevent memory issues
        $files = [
            database_path('sql/pengundi.sql'),
            database_path('sql/maps.sql'),
            
            database_path('sql/members.sql'),
            
        ];

        foreach ($files as $path) {

            if (!File::exists($path)) {
                $this->command->error("SQL file not found: {$path}");
                continue;
            }

            $this->command->info("Importing: " . basename($path));

            $sql = File::get($path);
            DB::unprepared($sql);

            $this->command->info("Imported successfully: " . basename($path));
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('All SQL files processed.');
        
    }
}