<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userRole = auth()->user()->role;
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        // Common data for all roles
        $lowStockParts = \App\Models\Part::where('quantity_stock', '<', 10)
            ->orderBy('quantity_stock', 'asc')
            ->limit(5)
            ->get();

        // Role-specific data
        if ($userRole === 'Administrator') {
            // Administrator: Business Overview & Performance
            $weeklyCustomers = \App\Models\Customer::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
            $weeklyIncome = \App\Models\Transaction::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->get()
                ->sum(fn ($t) => $t->amountPaidThisPayment());
            $weeklyServices = \App\Models\ServiceReport::whereBetween('date_in', [$startOfWeek, $endOfWeek])->count();

            $prevStart = now()->subWeek()->startOfWeek();
            $prevEnd = now()->subWeek()->endOfWeek();
            $prevCustomers = \App\Models\Customer::whereBetween('created_at', [$prevStart, $prevEnd])->count();
            $prevIncome = \App\Models\Transaction::whereBetween('created_at', [$prevStart, $prevEnd])
                ->get()
                ->sum(fn ($t) => $t->amountPaidThisPayment());
            $prevServices = \App\Models\ServiceReport::whereBetween('date_in', [$prevStart, $prevEnd])->count();

            $customerGrowth = $prevCustomers > 0 ? round((($weeklyCustomers - $prevCustomers) / $prevCustomers) * 100) : ($weeklyCustomers > 0 ? 100 : 0);
            $incomeGrowth = $prevIncome > 0 ? round((($weeklyIncome - $prevIncome) / $prevIncome) * 100) : ($weeklyIncome > 0 ? 100 : 0);
            $serviceGrowth = $prevServices > 0 ? round((($weeklyServices - $prevServices) / $prevServices) * 100) : ($weeklyServices > 0 ? 100 : 0);
            $overallGrowth = ($customerGrowth + $incomeGrowth + $serviceGrowth) > 0
                ? round(($customerGrowth + $incomeGrowth + $serviceGrowth) / 3)
                : 0;

            $recentServices = \App\Models\ServiceReport::with(['customer', 'appliance'])
                ->latest()
                ->limit(5)
                ->get();

            $recentTransactions = \App\Models\Transaction::with(['report.customer'])
                ->latest()
                ->limit(5)
                ->get();

            // Chart data
            $sixMonthsAgo = now()->subMonths(5)->startOfMonth();
            $reports = \App\Models\ServiceReport::with('details')->where('date_in', '>=', $sixMonthsAgo)->get();
            $serviceCounts = [];
            $monthlyCounts = [];
            $months = [];

            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i)->format('M');
                $months[] = $month;
                $monthlyCounts[$month] = [];
            }

            foreach ($reports as $report) {
                $month = \Carbon\Carbon::parse($report->date_in)->format('M');
                if (!isset($monthlyCounts[$month])) continue;

                $types = $report->details ? $report->details->service_types : [];
                if (empty($types) || !is_array($types)) {
                    $types = ['Other'];
                }

                foreach ($types as $type) {
                    if (!isset($serviceCounts[$type])) $serviceCounts[$type] = 0;
                    $serviceCounts[$type]++;

                    if (!isset($monthlyCounts[$month][$type])) $monthlyCounts[$month][$type] = 0;
                    $monthlyCounts[$month][$type]++;
                }
            }

            arsort($serviceCounts);
            $topTypes = array_slice(array_keys($serviceCounts), 0, 3);
            if (empty($topTypes)) {
                $topTypes = ['Air Conditioner Repair', 'Refrigerator Repair', 'Washing Machine Repair'];
            }

            $donutLabels = [];
            $donutData = [];
            foreach ($topTypes as $type) {
                $donutLabels[] = $type;
                $donutData[] = $serviceCounts[$type] ?? 0;
            }

            $lineDatasets = [];
            $colors = ['#F97316', '#22C55E', '#3B82F6'];
            $bgColors = ['rgba(249,115,22,0.08)', 'rgba(34,197,94,0.08)', 'rgba(59,130,246,0.08)'];

            foreach ($topTypes as $index => $type) {
                $data = [];
                foreach ($months as $month) {
                    $data[] = $monthlyCounts[$month][$type] ?? 0;
                }
                $lineDatasets[] = [
                    'label' => $type,
                    'data' => $data,
                    'borderColor' => $colors[$index % 3] ?? '#000',
                    'backgroundColor' => $bgColors[$index % 3] ?? 'rgba(0,0,0,0)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ];
            }

            $chartData = [
                'donutLabels' => $donutLabels,
                'donutData' => $donutData,
                'lineMonths' => $months,
                'lineDatasets' => $lineDatasets
            ];

            $partsUsage = \DB::table('part_service_report')
                ->join('parts', 'part_service_report.part_id', '=', 'parts.id')
                ->select('parts.name', \DB::raw('SUM(part_service_report.quantity) as total_quantity'))
                ->groupBy('parts.id', 'parts.name')
                ->orderByDesc('total_quantity')
                ->limit(10)
                ->get();

            $partsUsageChartData = [
                'labels' => $partsUsage->pluck('name')->toArray(),
                'data' => $partsUsage->pluck('total_quantity')->toArray(),
            ];

            return view('dashboard', compact(
                'weeklyCustomers',
                'weeklyIncome',
                'weeklyServices',
                'lowStockParts',
                'recentTransactions',
                'recentServices',
                'customerGrowth',
                'incomeGrowth',
                'serviceGrowth',
                'overallGrowth',
                'chartData',
                'partsUsageChartData',
                'userRole'
            ));

        } elseif ($userRole === 'Secretary') {
            // Secretary: Customer Operations & Inventory
            $weeklyCustomers = \App\Models\Customer::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
            $pendingServices = \App\Models\ServiceReport::where('status', 'Pending')->count();
            $inProgressServices = \App\Models\ServiceReport::whereIn('status', ['In Progress', 'Under Repair', 'Waiting for Parts'])->count();

            $prevStart = now()->subWeek()->startOfWeek();
            $prevEnd = now()->subWeek()->endOfWeek();
            $prevCustomers = \App\Models\Customer::whereBetween('created_at', [$prevStart, $prevEnd])->count();
            $customerGrowth = $prevCustomers > 0 ? round((($weeklyCustomers - $prevCustomers) / $prevCustomers) * 100) : ($weeklyCustomers > 0 ? 100 : 0);

            $recentCustomers = \App\Models\Customer::latest()->limit(5)->get();
            $recentServices = \App\Models\ServiceReport::with(['customer', 'appliance'])
                ->latest()
                ->limit(5)
                ->get();
            $pendingServicesList = \App\Models\ServiceReport::with(['customer', 'appliance'])
                ->where('status', 'Pending')
                ->latest()
                ->limit(5)
                ->get();

            return view('dashboard', compact(
                'weeklyCustomers',
                'pendingServices',
                'inProgressServices',
                'lowStockParts',
                'recentCustomers',
                'recentServices',
                'pendingServicesList',
                'customerGrowth',
                'userRole'
            ));

        } elseif ($userRole === 'Cashier') {
            // Cashier: Financial Transactions
            $dailyIncome = \App\Models\Transaction::whereDate('created_at', today())
                ->get()
                ->sum(fn ($t) => $t->amountPaidThisPayment());
            $weeklyIncome = \App\Models\Transaction::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->get()
                ->sum(fn ($t) => $t->amountPaidThisPayment());

            // Outstanding = remaining balance per unfinished report (avoid double-counting)
            $outstandingPayments = \App\Models\ServiceReport::with(['details', 'transactions'])
                ->where('status', 'Completed')
                ->whereDoesntHave('transactions', fn ($q) => $q->where('payment_status', 'Paid'))
                ->get()
                ->sum(function ($report) {
                    $bill = (float) ($report->details->total_amount ?? 0);
                    if ($bill <= 0) {
                        return 0;
                    }
                    $paid = $report->transactions->sum(fn ($t) => $t->amountPaidThisPayment());
                    return max(0, $bill - $paid);
                });

            $completedServices = \App\Models\ServiceReport::where('status', 'Completed')
                ->whereDoesntHave('transactions', fn ($q) => $q->where('payment_status', 'Paid'))
                ->count();

            $prevWeekIncome = \App\Models\Transaction::whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
                ->get()
                ->sum(fn ($t) => $t->amountPaidThisPayment());
            $incomeGrowth = $prevWeekIncome > 0 ? round((($weeklyIncome - $prevWeekIncome) / $prevWeekIncome) * 100) : ($weeklyIncome > 0 ? 100 : 0);

            $recentTransactions = \App\Models\Transaction::with(['report.customer'])
                ->latest()
                ->limit(10)
                ->get();

            $pendingInvoices = \App\Models\ServiceReport::where('status', 'Completed')
                ->whereDoesntHave('transactions', fn ($q) => $q->where('payment_status', 'Paid'))
                ->with(['customer', 'appliance', 'details', 'transactions'])
                ->latest()
                ->limit(5)
                ->get();

            return view('dashboard', compact(
                'dailyIncome',
                'weeklyIncome',
                'outstandingPayments',
                'completedServices',
                'incomeGrowth',
                'recentTransactions',
                'pendingInvoices',
                'userRole'
            ));

        } elseif ($userRole === 'Technician') {
            // Technician: Workload & Service Status
            $technicianName = trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? ''));
            
            // If technician name is empty, return empty data
            if (empty($technicianName)) {
                return view('dashboard', [
                    'assignedServices' => 0,
                    'inProgressServices' => 0,
                    'completedToday' => 0,
                    'myServices' => collect(),
                    'recentComments' => collect(),
                    'userRole' => $userRole
                ]);
            }

            $assignedServices = \App\Models\ServiceReport::whereHas('details', function($q) use ($technicianName) {
                $q->where('technician', 'like', "%{$technicianName}%");
            })->count();

            $inProgressServices = \App\Models\ServiceReport::whereHas('details', function($q) use ($technicianName) {
                $q->where('technician', 'like', "%{$technicianName}%");
            })->whereIn('status', ['In Progress', 'Under Repair', 'Waiting for Parts'])->count();

            $completedToday = \App\Models\ServiceReport::whereHas('details', function($q) use ($technicianName) {
                $q->where('technician', 'like', "%{$technicianName}%");
            })->where('status', 'Completed')
                ->whereDate('updated_at', today())
                ->count();

            $myServices = \App\Models\ServiceReport::with(['customer', 'appliance', 'comments'])
                ->whereHas('details', function($q) use ($technicianName) {
                    $q->where('technician', 'like', "%{$technicianName}%");
                })
                ->whereIn('status', ['In Progress', 'Under Repair', 'Waiting for Parts'])
                ->latest()
                ->limit(5)
                ->get();

            $recentComments = \App\Models\ServiceProgressComment::with(['report', 'user'])
                ->whereHas('report.details', function($q) use ($technicianName) {
                    $q->where('technician', 'like', "%{$technicianName}%");
                })
                ->latest()
                ->limit(5)
                ->get();

            return view('dashboard', compact(
                'assignedServices',
                'inProgressServices',
                'completedToday',
                'myServices',
                'recentComments',
                'userRole'
            ));
        }

        // Fallback for other roles
        return view('dashboard', compact('userRole'));
    }
}
