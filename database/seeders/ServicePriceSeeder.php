<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicePriceSeeder extends Seeder
{
    public function run(): void
    {
        $servicePrices = [
            [
                'service_name' => 'Air Conditioner Repair',
                'service_price' => 1500.00,
            ],
            [
                'service_name' => 'Refrigerator Repair',
                'service_price' => 1200.00,
            ],
            [
                'service_name' => 'Washing Machine Repair',
                'service_price' => 1000.00,
            ],
            [
                'service_name' => 'Microwave Oven Repair',
                'service_price' => 800.00,
            ],
            [
                'service_name' => 'Dishwasher Repair',
                'service_price' => 1300.00,
            ],
            [
                'service_name' => 'Electric Fan Repair',
                'service_price' => 500.00,
            ],
            [
                'service_name' => 'Water Heater Repair',
                'service_price' => 900.00,
            ],
            [
                'service_name' => 'Oven Repair',
                'service_price' => 1100.00,
            ],
            [
                'service_name' => 'Freezer Repair',
                'service_price' => 1400.00,
            ],
            [
                'service_name' => 'Air Purifier Repair',
                'service_price' => 700.00,
            ],
            [
                'service_name' => 'Coffee Maker Repair',
                'service_price' => 600.00,
            ],
            [
                'service_name' => 'Blender Repair',
                'service_price' => 400.00,
            ],
            [
                'service_name' => 'Iron Repair',
                'service_price' => 350.00,
            ],
            [
                'service_name' => 'Vacuum Cleaner Repair',
                'service_price' => 850.00,
            ],
            [
                'service_name' => 'Rice Cooker Repair',
                'service_price' => 450.00,
            ],
            [
                'service_name' => 'Toaster Repair',
                'service_price' => 300.00,
            ],
            [
                'service_name' => 'Food Processor Repair',
                'service_price' => 750.00,
            ],
            [
                'service_name' => 'Electric Kettle Repair',
                'service_price' => 350.00,
            ],
            [
                'service_name' => 'Hair Dryer Repair',
                'service_price' => 400.00,
            ],
            [
                'service_name' => 'General Appliance Inspection',
                'service_price' => 500.00,
            ],
        ];

        foreach ($servicePrices as $servicePrice) {
            DB::table('service_prices')->insert($servicePrice);
        }
    }
}
