<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create an Admin User (if not exists)
        if (!User::where('username', 'admin')->exists()) {
            User::create([
                'first_name' => 'Admin',
                'last_name' => 'User',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'Administrator',
                'status' => 'Active',
                'email_verified_at' => now(),
            ]);
        }

        // Create a Technician User (if not exists)
        if (!User::where('username', 'technician')->exists()) {
            User::create([
                'first_name' => 'John',
                'last_name' => 'Tech',
                'username' => 'technician',
                'email' => 'tech@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'Technician',
                'status' => 'Active',
                'email_verified_at' => now(),
            ]);
        }

        // Call custom seeders
        $this->call([
            PartSeeder::class,
            CustomerSeeder::class,
            ServiceSeeder::class,
            TransactionSeeder::class,
            ServicePriceSeeder::class,
            TestUserSeeder::class,
        ]);
    }
}
