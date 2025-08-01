<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Zafeer',
        //     'email' => 'zafeer@devop360.com',
        //     'email_verified_at' => now(),
        //     'password' => Hash::make('devop360'),
        //     'remember_token' => Str::random(10),
        // ]);

        // Create and assign role to manager
        $manager = User::create([
            'name' => 'Test Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
        ]);
        $manager->assignRole(Role::MANAGER->value);
    }
}
