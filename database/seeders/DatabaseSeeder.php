<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The roles the app relies on. The first admin is created with
     * `php artisan helpdesk:install` (no default password is ever seeded).
     */
    public function run(): void
    {
        foreach (['Admin', 'Member', 'Viewer'] as $name) {
            Role::firstOrCreate(['name' => $name]);
        }
    }
}
