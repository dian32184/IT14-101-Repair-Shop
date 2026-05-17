<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\ServiceReport;
use App\Models\ServiceDetail;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $serviceReports = ServiceReport::with('details')->get();
        
        if ($serviceReports->isEmpty()) {
            $this->command->warn('No service reports found. Please run ServiceSeeder first.');
            return;
        }

        $transactions = [
            [
                'payment_status' => 'Paid',
                'payment_method' => 'Cash',
                'payment_date' => now()->subDays(5),
                'payment_due' => now()->subDays(3),
                'reference_no' => 'CASH-001',
            ],
            [
                'payment_status' => 'Paid',
                'payment_method' => 'GCash',
                'payment_date' => now()->subDays(10),
                'payment_due' => now()->subDays(8),
                'reference_no' => 'GCASH-002',
            ],
            [
                'payment_status' => 'Paid',
                'payment_method' => 'Bank Transfer',
                'payment_date' => now()->subDays(15),
                'payment_due' => now()->subDays(12),
                'reference_no' => 'BANK-003',
            ],
            [
                'payment_status' => 'Paid',
                'payment_method' => 'Cash',
                'payment_date' => now()->subDays(20),
                'payment_due' => now()->subDays(18),
                'reference_no' => 'CASH-004',
            ],
            [
                'payment_status' => 'Paid',
                'payment_method' => 'PayMongo',
                'payment_date' => now()->subDays(7),
                'payment_due' => now()->subDays(5),
                'reference_no' => 'PM-005',
            ],
            [
                'payment_status' => 'Partial',
                'payment_method' => 'Cash',
                'payment_date' => now()->subDays(3),
                'payment_due' => now()->addDays(7),
                'reference_no' => 'CASH-006',
                'partial_payment_amount' => 1000,
            ],
            [
                'payment_status' => 'Paid',
                'payment_method' => 'GCash',
                'payment_date' => now()->subDays(12),
                'payment_due' => now()->subDays(10),
                'reference_no' => 'GCASH-007',
            ],
            [
                'payment_status' => 'Unpaid',
                'payment_method' => null,
                'payment_date' => null,
                'payment_due' => now()->addDays(5),
                'reference_no' => null,
            ],
            [
                'payment_status' => 'Paid',
                'payment_method' => 'Cash',
                'payment_date' => now()->subDays(8),
                'payment_due' => now()->subDays(6),
                'reference_no' => 'CASH-009',
            ],
            [
                'payment_status' => 'Partial',
                'payment_method' => 'PayMongo',
                'payment_date' => now()->subDays(2),
                'payment_due' => now()->addDays(10),
                'reference_no' => 'PM-010',
                'partial_payment_amount' => 1500,
            ],
        ];

        foreach ($serviceReports as $index => $report) {
            if (!isset($transactions[$index])) {
                continue;
            }

            $transactionData = $transactions[$index];
            $detail = $report->details;

            if (!$detail) {
                continue;
            }

            Transaction::create([
                'report_id' => $report->id,
                'parts_total' => $detail->parts_total_charge,
                'labor_total' => $detail->labor,
                'total_amount' => $detail->total_amount,
                'payment_status' => $transactionData['payment_status'],
                'payment_method' => $transactionData['payment_method'],
                'partial_payment_amount' => $transactionData['partial_payment_amount'] ?? null,
                'reference_no' => $transactionData['reference_no'],
                'payment_date' => $transactionData['payment_date'],
                'payment_due' => $transactionData['payment_due'],
                'received_by' => 'Admin User',
            ]);
        }
    }
}
