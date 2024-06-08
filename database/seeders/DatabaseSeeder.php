<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'condidate User',
            'email' => 'test@example.com',
            'role' => 'condidate',
        ]);

        User::factory()->create([
            'name' => 'admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Gestionnaire User',
            'email' => 'gestionnaire@example.com',
            'role' => 'Gestionnaire',
        ]);

        User::factory()->create([
            'name' => 'jury User',
            'email' => 'jury@example.com',
            'role' => 'comite',
        ]);
    }
}
