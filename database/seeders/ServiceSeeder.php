<?php

namespace Database\Seeders;

use App\Models\ServiceReport;
use App\Models\ServiceDetail;
use App\Models\Part;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $customers = \App\Models\Customer::with('appliances')->get();
        
        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Please run CustomerSeeder first.');
            return;
        }

        $parts = Part::all();
        
        if ($parts->isEmpty()) {
            $this->command->warn('No parts found. Please run PartSeeder first.');
            return;
        }

        $services = [
            [
                'status' => 'Completed',
                'findings' => 'Compressor motor failure. Needs replacement.',
                'remarks' => 'Replaced compressor, system working properly.',
                'service_types' => ['Air Conditioner Repair'],
                'labor_cost' => 500,
                'miscellaneous_cost' => 200,
                'parts_needed' => ['P-001', 'P-003', 'P-006'], // Compressor Motor, Thermostat, Capacitor
            ],
            [
                'status' => 'Completed',
                'findings' => 'Fan blade broken due to wear and tear.',
                'remarks' => 'Replaced fan blade, airflow restored.',
                'service_types' => ['Air Conditioner Repair'],
                'labor_cost' => 300,
                'miscellaneous_cost' => 100,
                'parts_needed' => ['P-002', 'P-015'], // Fan Blade, Filter
            ],
            [
                'status' => 'Completed',
                'findings' => 'Condenser coil leaking refrigerant.',
                'remarks' => 'Replaced condenser coil, recharged refrigerant.',
                'service_types' => ['Refrigerator Repair'],
                'labor_cost' => 600,
                'miscellaneous_cost' => 300,
                'parts_needed' => ['P-004', 'P-016'], // Condenser Coil, Thermistor
            ],
            [
                'status' => 'Completed',
                'findings' => 'Evaporator coil frozen, defrost system malfunction.',
                'remarks' => 'Replaced evaporator coil, defrost system repaired.',
                'service_types' => ['Refrigerator Repair'],
                'labor_cost' => 550,
                'miscellaneous_cost' => 250,
                'parts_needed' => ['P-005', 'P-011'], // Evaporator Coil, Door Gasket
            ],
            [
                'status' => 'Completed',
                'findings' => 'Capacitor burnt out, compressor not starting.',
                'remarks' => 'Replaced capacitor, system operational.',
                'service_types' => ['Air Conditioner Repair'],
                'labor_cost' => 350,
                'miscellaneous_cost' => 150,
                'parts_needed' => ['P-006', 'P-007'], // Capacitor, Relay Switch
            ],
            [
                'status' => 'Completed',
                'findings' => 'Washing machine not draining properly.',
                'remarks' => 'Replaced water pump and drain hose.',
                'service_types' => ['Washing Machine Repair'],
                'labor_cost' => 400,
                'miscellaneous_cost' => 200,
                'parts_needed' => ['P-008', 'P-012', 'P-018'], // Water Pump, Drain Hose, Pressure Switch
            ],
            [
                'status' => 'Completed',
                'findings' => 'Heating element not heating properly.',
                'remarks' => 'Replaced heating element, heating function restored.',
                'service_types' => ['Oven Repair'],
                'labor_cost' => 450,
                'miscellaneous_cost' => 200,
                'parts_needed' => ['P-009', 'P-016'], // Heating Element, Thermistor
            ],
            [
                'status' => 'Completed',
                'findings' => 'Control board malfunction, erratic behavior.',
                'remarks' => 'Replaced control board, all functions working.',
                'service_types' => ['Washing Machine Repair'],
                'labor_cost' => 700,
                'miscellaneous_cost' => 350,
                'parts_needed' => ['P-010', 'P-019'], // Control Board, Timer Knob
            ],
            [
                'status' => 'Completed',
                'findings' => 'Washing machine belt broken, motor not spinning.',
                'remarks' => 'Replaced belt and motor brushes.',
                'service_types' => ['Washing Machine Repair'],
                'labor_cost' => 350,
                'miscellaneous_cost' => 150,
                'parts_needed' => ['P-013', 'P-014'], // Belt, Motor Brush
            ],
            [
                'status' => 'Completed',
                'findings' => 'Dishwasher not filling with water properly.',
                'remarks' => 'Replaced solenoid valve and door lock assembly.',
                'service_types' => ['Dishwasher Repair'],
                'labor_cost' => 500,
                'miscellaneous_cost' => 250,
                'parts_needed' => ['P-017', 'P-020'], // Solenoid Valve, Door Lock Assembly
            ],
        ];

        foreach ($services as $index => $serviceData) {
            // Get a random customer with appliances
            $customer = $customers[$index % $customers->count()];
            $appliance = $customer->appliances->first();

            if (!$appliance) {
                continue;
            }

            $partsNeeded = $serviceData['parts_needed'];
            unset($serviceData['parts_needed']);

            // Calculate parts total
            $partsTotal = 0;
            $partsData = [];

            foreach ($partsNeeded as $partNo) {
                $part = $parts->where('part_no', $partNo)->first();
                if ($part) {
                    $quantity = rand(1, 2);
                    $partsTotal += $part->price * $quantity;
                    $partsData[$part->id] = [
                        'quantity' => $quantity,
                        'price' => $part->price,
                    ];
                }
            }

            $totalAmount = $serviceData['labor_cost'] + $partsTotal + $serviceData['miscellaneous_cost'];

            // Create service report
            $report = ServiceReport::create([
                'customer_id' => $customer->id,
                'customer_name' => trim($customer->first_name . ' ' . $customer->last_name),
                'appliance_id' => $appliance->id,
                'date_in' => now()->subDays(rand(1, 30)),
                'status' => $serviceData['status'],
                'findings' => $serviceData['findings'],
                'remarks' => $serviceData['remarks'],
            ]);

            // Attach parts
            $report->parts()->sync($partsData);

            // Create service detail
            ServiceDetail::create([
                'report_id' => $report->id,
                'service_types' => $serviceData['service_types'],
                'labor' => $serviceData['labor_cost'],
                'parts_total_charge' => $partsTotal,
                'miscellaneous_cost' => $serviceData['miscellaneous_cost'],
                'total_amount' => $totalAmount,
                'complaint' => $serviceData['findings'],
                'technician' => 'John Tech',
                'date_repaired' => $serviceData['status'] === 'Completed' ? now()->subDays(rand(1, 5)) : null,
                'date_delivered' => $serviceData['status'] === 'Completed' ? now()->subDays(rand(0, 3)) : null,
            ]);
        }
    }
}
