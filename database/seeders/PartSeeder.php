<?php

namespace Database\Seeders;

use App\Models\Part;
use Illuminate\Database\Seeder;

class PartSeeder extends Seeder
{
    public function run(): void
    {
        $parts = [
            [
                'part_no' => 'P-001',
                'name' => 'Compressor Motor',
                'price' => 2500.00,
                'quantity_stock' => 5,
            ],
            [
                'part_no' => 'P-002',
                'name' => 'Fan Blade',
                'price' => 450.00,
                'quantity_stock' => 12,
            ],
            [
                'part_no' => 'P-003',
                'name' => 'Thermostat',
                'price' => 800.00,
                'quantity_stock' => 8,
            ],
            [
                'part_no' => 'P-004',
                'name' => 'Condenser Coil',
                'price' => 1800.00,
                'quantity_stock' => 6,
            ],
            [
                'part_no' => 'P-005',
                'name' => 'Evaporator Coil',
                'price' => 1500.00,
                'quantity_stock' => 7,
            ],
            [
                'part_no' => 'P-006',
                'name' => 'Capacitor',
                'price' => 350.00,
                'quantity_stock' => 15,
            ],
            [
                'part_no' => 'P-007',
                'name' => 'Relay Switch',
                'price' => 250.00,
                'quantity_stock' => 20,
            ],
            [
                'part_no' => 'P-008',
                'name' => 'Water Pump',
                'price' => 1200.00,
                'quantity_stock' => 4,
            ],
            [
                'part_no' => 'P-009',
                'name' => 'Heating Element',
                'price' => 950.00,
                'quantity_stock' => 9,
            ],
            [
                'part_no' => 'P-010',
                'name' => 'Control Board',
                'price' => 2200.00,
                'quantity_stock' => 3,
            ],
            [
                'part_no' => 'P-011',
                'name' => 'Door Gasket',
                'price' => 600.00,
                'quantity_stock' => 18,
            ],
            [
                'part_no' => 'P-012',
                'name' => 'Drain Hose',
                'price' => 200.00,
                'quantity_stock' => 25,
            ],
            [
                'part_no' => 'P-013',
                'name' => 'Belt',
                'price' => 350.00,
                'quantity_stock' => 14,
            ],
            [
                'part_no' => 'P-014',
                'name' => 'Motor Brush',
                'price' => 150.00,
                'quantity_stock' => 30,
            ],
            [
                'part_no' => 'P-015',
                'name' => 'Filter',
                'price' => 400.00,
                'quantity_stock' => 22,
            ],
            [
                'part_no' => 'P-016',
                'name' => 'Thermistor',
                'price' => 550.00,
                'quantity_stock' => 11,
            ],
            [
                'part_no' => 'P-017',
                'name' => 'Solenoid Valve',
                'price' => 750.00,
                'quantity_stock' => 8,
            ],
            [
                'part_no' => 'P-018',
                'name' => 'Pressure Switch',
                'price' => 450.00,
                'quantity_stock' => 13,
            ],
            [
                'part_no' => 'P-019',
                'name' => 'Timer Knob',
                'price' => 300.00,
                'quantity_stock' => 16,
            ],
            [
                'part_no' => 'P-020',
                'name' => 'Door Lock Assembly',
                'price' => 850.00,
                'quantity_stock' => 7,
            ],
        ];

        foreach ($parts as $part) {
            Part::create($part);
        }
    }
}
