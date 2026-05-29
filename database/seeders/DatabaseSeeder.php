<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * D-09: only runs DevDataSeeder in local/testing environments.
     * Production is a strict no-op — AdminSeeder (Plan 05) is called
     * explicitly via the deploy hook, never through this entrypoint.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            if (isset($this->command)) {
                $this->command->info('DatabaseSeeder skipped — production environment');
            }
            return;
        }

        $this->call(DevDataSeeder::class);
    }
}
