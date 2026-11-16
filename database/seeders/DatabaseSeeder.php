<?php

namespace Database\Seeders;

use App\Support\Sandbox;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Fill a fresh database: roles, accounts, categories, the shelves and a
     * community of sample readers.   php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        // Seeded records are public: the demo sandbox must not tag them as a visitor's.
        Sandbox::pause(function () {
            $this->call([
                RoleSeeder::class,
                UserSeeder::class,
                CategorySeeder::class,
                LibrarySeeder::class,
                CommunitySeeder::class,
            ]);
        });
    }
}
