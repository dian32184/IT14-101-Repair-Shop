<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');
        $adminName = env('ADMIN_NAME', 'Administrator');

        // Skip if env vars are not set
        if (empty($adminEmail) || empty($adminPassword)) {
            $this->command->info('ADMIN_EMAIL or ADMIN_PASSWORD not set. Skipping admin user creation.');
            return;
        }

        // Build attributes based on actual schema
        $attributes = [
            'username' => 'admin',
            'email' => $adminEmail,
            'password' => Hash::make($adminPassword),
            'role' => 'Administrator',
            'status' => 'Active',
        ];

        // Set name columns based on what exists in schema
        if (Schema::hasColumn('users', 'first_name') && Schema::hasColumn('users', 'last_name')) {
            // Split admin name into first and last
            $nameParts = explode(' ', $adminName, 2);
            $attributes['first_name'] = $nameParts[0];
            $attributes['last_name'] = $nameParts[1] ?? '';
        } elseif (Schema::hasColumn('users', 'full_name')) {
            $attributes['full_name'] = $adminName;
        }

        // Set email_verified_at if column exists (dashboard uses verified middleware)
        if (Schema::hasColumn('users', 'email_verified_at')) {
            $attributes['email_verified_at'] = now();
        }

        // Create or update admin user using email as the unique key (login uses email)
        User::unguarded(function () use ($adminEmail, $attributes) {
            $user = User::where('email', $adminEmail)->first();
            
            if ($user) {
                $user->forceFill($attributes)->save();
                $this->command->info('Admin user updated successfully.');
            } else {
                User::create($attributes);
                $this->command->info('Admin user created successfully.');
            }
        });
    }
}
