<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Appliance;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'address' => '123 Rizal Street, Manila',
                'phone_no' => '09171234567',
                'appliances' => [
                    [
                        'brand' => 'Samsung',
                        'category' => 'Cooling',
                        'product' => 'Refrigerator',
                        'model_no' => 'SAM-REF-123',
                        'serial_no' => 'SN123456',
                        'appliance_size' => 'Large',
                        'warranty_end' => now()->addMonths(6),
                    ],
                    [
                        'brand' => 'LG',
                        'category' => 'Cooling',
                        'product' => 'Air Conditioner',
                        'model_no' => 'LG-AC-456',
                        'serial_no' => 'SN789012',
                        'appliance_size' => 'Medium',
                        'warranty_end' => now()->addMonths(3),
                    ],
                ],
            ],
            [
                'first_name' => 'Juan',
                'last_name' => 'Reyes',
                'address' => '456 Mabini Street, Quezon City',
                'phone_no' => '09187654321',
                'appliances' => [
                    [
                        'brand' => 'Panasonic',
                        'category' => 'Laundry',
                        'product' => 'Washing Machine',
                        'model_no' => 'PAN-WM-789',
                        'serial_no' => 'SN345678',
                        'appliance_size' => 'Medium',
                        'warranty_end' => now()->addMonths(3),
                    ],
                ],
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Garcia',
                'address' => '789 Bonifacio Street, Makati',
                'phone_no' => '09192345678',
                'appliances' => [
                    [
                        'brand' => 'Whirlpool',
                        'category' => 'Cooling',
                        'product' => 'Refrigerator',
                        'model_no' => 'WHI-REF-321',
                        'serial_no' => 'SN567890',
                        'appliance_size' => 'Large',
                        'warranty_end' => now()->addMonths(6),
                    ],
                    [
                        'brand' => 'Sharp',
                        'category' => 'Cooking',
                        'product' => 'Microwave Oven',
                        'model_no' => 'SH-MW-654',
                        'serial_no' => 'SN901234',
                        'appliance_size' => 'Small',
                        'warranty_end' => now()->addMonths(1),
                    ],
                ],
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Mendoza',
                'address' => '321 Aguinaldo Street, Pasig',
                'phone_no' => '09203456789',
                'appliances' => [
                    [
                        'brand' => 'Electrolux',
                        'category' => 'Laundry',
                        'product' => 'Washing Machine',
                        'model_no' => 'ELE-WM-987',
                        'serial_no' => 'SN234567',
                        'appliance_size' => 'Large',
                        'warranty_end' => now()->addMonths(6),
                    ],
                ],
            ],
            [
                'first_name' => 'Elena',
                'last_name' => 'Ramos',
                'address' => '654 Del Pilar Street, Taguig',
                'phone_no' => '09214567890',
                'appliances' => [
                    [
                        'brand' => 'Carrier',
                        'category' => 'Cooling',
                        'product' => 'Air Conditioner',
                        'model_no' => 'CAR-AC-246',
                        'serial_no' => 'SN678901',
                        'appliance_size' => 'Medium',
                        'warranty_end' => now()->addMonths(3),
                    ],
                    [
                        'brand' => 'Haier',
                        'category' => 'Cooling',
                        'product' => 'Refrigerator',
                        'model_no' => 'HAI-REF-135',
                        'serial_no' => 'SN012345',
                        'appliance_size' => 'Small',
                        'warranty_end' => now()->addMonths(1),
                    ],
                ],
            ],
        ];

        foreach ($customers as $customerData) {
            $appliances = $customerData['appliances'];
            unset($customerData['appliances']);

            $customer = Customer::create($customerData);

            foreach ($appliances as $applianceData) {
                Appliance::create(array_merge($applianceData, [
                    'customer_id' => $customer->id,
                    'date_in' => now()->subMonths(rand(1, 12)),
                ]));
            }
        }
    }
}
