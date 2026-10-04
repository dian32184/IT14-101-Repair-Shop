<?php

namespace Database\Seeders;

use App\Models\ServiceReport;
use App\Models\ServiceDetail;
use App\Models\ServiceProgressComment;
use App\Models\Customer;
use App\Models\Part;
use Illuminate\Database\Seeder;

class TechnicianDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $technicianName = 'Technician Test';
        
        $customers = Customer::with('appliances')->get();
        
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
                'status' => 'In Progress',
                'findings' => 'Air conditioner not cooling properly. Refrigerant leak detected.',
                'remarks' => 'Currently repairing the leak and recharging refrigerant.',
                'service_types' => ['Air Conditioner Repair'],
                'labor_cost' => 450,
                'miscellaneous_cost' => 100,
                'parts_needed' => ['P-003', 'P-006'],
                'date_in_offset' => 1,
            ],
            [
                'status' => 'Under Repair',
                'findings' => 'Washing machine making loud noise during spin cycle.',
                'remarks' => 'Disassembled drum, replacing worn bearings.',
                'service_types' => ['Washing Machine Repair'],
                'labor_cost' => 350,
                'miscellaneous_cost' => 75,
                'parts_needed' => ['P-013', 'P-014'],
                'date_in_offset' => 2,
            ],
            [
                'status' => 'Waiting for Parts',
                'findings' => 'Refrigerator compressor failure. Needs replacement.',
                'remarks' => 'Ordered new compressor, awaiting delivery.',
                'service_types' => ['Refrigerator Repair'],
                'labor_cost' => 550,
                'miscellaneous_cost' => 150,
                'parts_needed' => ['P-001'],
                'date_in_offset' => 3,
            ],
            [
                'status' => 'In Progress',
                'findings' => 'Dishwasher not draining water properly.',
                'remarks' => 'Cleaned drain filter, checking pump assembly.',
                'service_types' => ['Dishwasher Repair'],
                'labor_cost' => 300,
                'miscellaneous_cost' => 50,
                'parts_needed' => ['P-008'],
                'date_in_offset' => 0,
            ],
            [
                'status' => 'Under Repair',
                'findings' => 'Oven temperature not accurate. Thermostat issue.',
                'remarks' => 'Testing thermostat, may need calibration or replacement.',
                'service_types' => ['Oven Repair'],
                'labor_cost' => 400,
                'miscellaneous_cost' => 100,
                'parts_needed' => ['P-016'],
                'date_in_offset' => 4,
            ],
        ];

        $comments = [
            'Started diagnosis on the unit.',
            'Found the issue, beginning repair work.',
            'Waiting for parts to arrive.',
            'Parts received, continuing repair.',
            'Almost finished with the repair.',
            'Testing the unit before delivery.',
            'Repair completed successfully.',
            'Customer notified about progress.',
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
                'date_in' => now()->subDays($serviceData['date_in_offset']),
                'status' => $serviceData['status'],
                'findings' => $serviceData['findings'],
                'remarks' => $serviceData['remarks'],
            ]);

            // Attach parts
            $report->parts()->sync($partsData);

            // Create service detail with technician assigned
            ServiceDetail::create([
                'report_id' => $report->id,
                'service_types' => $serviceData['service_types'],
                'labor' => $serviceData['labor_cost'],
                'parts_total_charge' => $partsTotal,
                'miscellaneous_cost' => $serviceData['miscellaneous_cost'],
                'total_amount' => $totalAmount,
                'complaint' => $serviceData['findings'],
                'technician' => $technicianName,
                'date_repaired' => $serviceData['status'] === 'Completed' ? now()->subDays(rand(1, 5)) : null,
                'date_delivered' => $serviceData['status'] === 'Completed' ? now()->subDays(rand(0, 3)) : null,
            ]);

            // Add progress comments
            $numComments = rand(2, 4);
            for ($i = 0; $i < $numComments; $i++) {
                $commentIndex = array_rand($comments);
                ServiceProgressComment::create([
                    'report_id' => $report->id,
                    'progress_key' => 'technician_update',
                    'comment_text' => $comments[$commentIndex],
                    'created_by' => null, // No specific user
                    'created_by_name' => $technicianName,
                    'created_at' => now()->subHours(rand(1, 24 * $serviceData['date_in_offset'])),
                ]);
            }

            $this->command->info("Created service #{$report->id} for technician: {$technicianName}");
        }

        // Add one completed service from today
        $customer = $customers->first();
        $appliance = $customer->appliances->first();
        
        if ($appliance) {
            $report = ServiceReport::create([
                'customer_id' => $customer->id,
                'customer_name' => trim($customer->first_name . ' ' . $customer->last_name),
                'appliance_id' => $appliance->id,
                'date_in' => today(),
                'status' => 'Completed',
                'findings' => 'Quick fix: loose connection on power cord.',
                'remarks' => 'Repaired connection, tested and working.',
            ]);

            ServiceDetail::create([
                'report_id' => $report->id,
                'service_types' => ['General Repair'],
                'labor' => 200,
                'parts_total_charge' => 0,
                'miscellaneous_cost' => 50,
                'total_amount' => 250,
                'complaint' => 'Loose connection on power cord.',
                'technician' => $technicianName,
                'date_repaired' => today(),
                'date_delivered' => today(),
            ]);

            ServiceProgressComment::create([
                'report_id' => $report->id,
                'progress_key' => 'technician_update',
                'comment_text' => 'Quick repair completed successfully.',
                'created_by' => null,
                'created_by_name' => $technicianName,
                'created_at' => now(),
            ]);

            $this->command->info("Created completed service #{$report->id} for today");
        }

        $this->command->info("\nTechnician Dashboard Test Data Created Successfully!");
        $this->command->info("Technician name: {$technicianName}");
        $this->command->info("Login with username: technician_test, password: password");
    }
}
