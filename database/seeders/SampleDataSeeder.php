<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if data already exists (prevent duplicate seeding)
        if (DB::table('parts')->exists() || DB::table('customers')->exists()) {
            $this->command->info('Sample data already exists. Skipping seeding.');
            return;
        }

        $this->command->info('Starting sample data seeding...');

        // Safe order: independent seeders first, then dependent ones
        $this->call([
            PartSeeder::class,
            ServicePriceSeeder::class,
            ApplianceTypeSeeder::class,
            CustomerSeeder::class,
            ServiceSeeder::class,
            TransactionSeeder::class,
        ]);

        $this->command->info('Sample data seeded successfully.');
    }
}
