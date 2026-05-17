<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $testUsers = [
            [
                'first_name' => 'Admin',
                'last_name' => 'Test',
                'username' => 'admin_test',
                'email' => 'admin@test.com',
                'password' => 'password',
                'role' => 'Administrator',
                'status' => 'Active',
            ],
            [
                'first_name' => 'Secretary',
                'last_name' => 'Test',
                'username' => 'secretary_test',
                'email' => 'secretary@test.com',
                'password' => 'password',
                'role' => 'Secretary',
                'status' => 'Active',
            ],
            [
                'first_name' => 'Technician',
                'last_name' => 'Test',
                'username' => 'technician_test',
                'email' => 'technician@test.com',
                'password' => 'password',
                'role' => 'Technician',
                'status' => 'Active',
            ],
            [
                'first_name' => 'Cashier',
                'last_name' => 'Test',
                'username' => 'cashier_test',
                'email' => 'cashier@test.com',
                'password' => 'password',
                'role' => 'Cashier',
                'status' => 'Active',
            ],
        ];

        foreach ($testUsers as $userData) {
            // Check if user already exists
            if (!User::where('username', $userData['username'])->exists()) {
                User::create([
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'username' => $userData['username'],
                    'email' => $userData['email'],
                    'password' => \Illuminate\Support\Facades\Hash::make($userData['password']),
                    'role' => $userData['role'],
                    'status' => $userData['status'],
                    'email_verified_at' => now(),
                ]);
                $this->command->info("Created test user: {$userData['username']} ({$userData['role']})");
            } else {
                $this->command->warn("Test user already exists: {$userData['username']}");
            }
        }

        $this->command->info("\nTest Users Created Successfully!");
        $this->command->info("All test users have password: 'password'");
    }
}
