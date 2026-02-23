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
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Vendor User',
            'email' => 'vendor@example.com',
            'role' => 'vendor',
        ]);

        User::factory()->create([
            'name' => 'Customer User',
            'email' => 'user@example.com',
            'role' => 'user',
        ]);
    }
}
