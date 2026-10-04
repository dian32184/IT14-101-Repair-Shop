<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TransactionController extends Controller
{
    private function checkTransactionAccess()
    {
        if (auth()->check() && !in_array(auth()->user()->role, ['Administrator', 'Cashier'])) {
            abort(403, 'Unauthorized. Only Cashiers and Administrators can manage Transactions.');
        }
    }

    private function applyWarrantyIfPaid(\App\Models\Transaction $transaction)
    {
        if ($transaction->payment_status !== 'Paid') {
            return;
        }

        $report = $transaction->report;
        if ($report && $report->appliance) {
            $months = 0;
            $size = $report->appliance->appliance_size;
            if ($size === 'Small') {
                $months = 1;
            } elseif ($size === 'Medium') {
                $months = 3;
            } elseif ($size === 'Large') {
                $months = 6;
            }

            if ($months > 0) {
                $report->appliance->update([
                    'warranty_end' => now()->addMonths($months),
                ]);
            }
        }
    }

    /**
     * Completed reports that still owe money (never paid or partial).
     */
    private function payableReports()
    {
        return \App\Models\ServiceReport::with(['customer', 'appliance', 'details', 'transactions'])
            ->where('status', 'Completed')
            ->whereDoesntHave('transactions', function ($query) {
                $query->where('payment_status', 'Paid');
            })
            ->latest()
            ->get()
            ->filter(function ($report) {
                $bill = (float) ($report->details->total_amount ?? 0);
                if ($bill <= 0) {
                    // No bill yet — eligible for first payment setup
                    return $report->transactions->isEmpty();
                }
                $paid = $report->transactions->sum(fn ($t) => $t->amountPaidThisPayment());
                return $paid < $bill;
            })
            ->values();
    }

    public function index(Request $request)
    {
        $this->checkTransactionAccess();

        $search = $request->input('search');
        $date = $request->input('date');
        $status = $request->input('status');
        $receivedBy = $request->input('received_by');

        $transactions = \App\Models\Transaction::with('report')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('id', 'like', "%$search%")
                        ->orWhere('receipt_no', 'like', "%$search%")
                        ->orWhereDate('payment_date', 'like', "%$search%")
                        ->orWhereDate('payment_due', 'like', "%$search%")
                        ->orWhereHas('report', function ($subQuery) use ($search) {
                            $subQuery->where('customer_name', 'like', "%$search%");
                        });
                });
            })
            ->when($date, function ($q) use ($date) {
                $q->where(function ($query) use ($date) {
                    $query->whereDate('payment_date', $date)
                        ->orWhereDate('payment_due', $date);
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->where('payment_status', $status);
            })
            ->when($receivedBy, function ($q) use ($receivedBy) {
                if ($receivedBy === 'System') {
                    $q->where(function ($query) {
                        $query->where('received_by', 'System')->orWhereNull('received_by');
                    });
                } elseif (in_array($receivedBy, ['Administrator', 'Secretary', 'Cashier'])) {
                    $userNames = \App\Models\User::where('role', $receivedBy)
                        ->get()
                        ->map(fn ($user) => trim($user->first_name . ' ' . $user->last_name))
                        ->filter()
                        ->toArray();

                    if (!empty($userNames)) {
                        $q->whereIn('received_by', $userNames);
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                } else {
                    $q->where('received_by', $receivedBy);
                }
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('transactions.index', compact('transactions', 'search', 'date', 'status', 'receivedBy'));
    }

    public function create()
    {
        $this->checkTransactionAccess();
        $reports = $this->payableReports();

        $reportsPayload = $reports->map(function ($r) {
            $bill = (float) ($r->details->total_amount ?? 0);
            $paid = $r->transactions->sum(fn ($t) => $t->amountPaidThisPayment());
            $hasPayments = $r->transactions->isNotEmpty();

            return [
                'id' => $r->id,
                'labor' => $r->details ? (float) $r->details->labor : 0,
                'materials' => $r->details ? (float) $r->details->parts_total_charge : 0,
                'delivery' => $r->details ? (float) $r->details->pullout_delivery : 0,
                'bill_total' => $bill,
                'already_paid' => $paid,
                'remaining' => max(0, ($bill > 0 ? $bill : 0) - $paid),
                'has_payments' => $hasPayments,
                'customer_name' => $r->customer_name,
            ];
        });

        return view('transactions.create', compact('reports', 'reportsPayload'));
    }

    public function store(Request $request)
    {
        $this->checkTransactionAccess();

        $validated = $request->validate([
            'report_id' => 'required|exists:service_reports,id',
            'labor' => 'nullable|numeric|min:300',
            'materials' => 'nullable|numeric|min:0',
            'delivery' => 'nullable|numeric|min:0',
            'payment_status' => 'required|string|in:Paid,Unpaid,Partial',
            'payment_method' => 'nullable|string',
            'amount_paid' => 'nullable|numeric|min:0',
            'partial_payment_amount' => 'nullable|numeric|min:0',
            'reference_no' => 'nullable|string',
            'received_by' => 'nullable|string',
            'payment_date' => 'nullable|date',
            'payment_due' => 'nullable|date',
        ], [
            'labor.min' => 'Labor cost must be at least ₱300.',
        ]);

        $report = \App\Models\ServiceReport::with(['details', 'transactions'])->findOrFail($validated['report_id']);

        if ($report->status !== 'Completed') {
            return back()->withInput()->with('error', 'Only Completed service reports can be paid.');
        }

        if ($report->transactions->contains(fn ($t) => $t->payment_status === 'Paid')) {
            return back()->withInput()->with('error', 'This service report is already fully paid.');
        }

        $hasPriorPayments = $report->transactions->isNotEmpty();
        $alreadyPaid = \App\Models\Transaction::totalPaidForReport($report->id);

        if ($hasPriorPayments) {
            $totalAmount = (float) ($report->details->total_amount ?? 0);
            if ($totalAmount <= 0) {
                return back()->withInput()->with('error', 'Bill total is missing for this report.');
            }
            $labor = (float) ($report->details->labor ?? 0);
            $materials = (float) ($report->details->parts_total_charge ?? 0);
            $delivery = (float) ($report->details->pullout_delivery ?? 0);
        } else {
            $request->validate([
                'labor' => 'required|numeric|min:300',
                'materials' => 'required|numeric|min:0',
            ], [
                'labor.min' => 'Labor cost must be at least ₱300.',
            ]);

            $labor = (float) $validated['labor'];
            $materials = (float) $validated['materials'];
            $delivery = (float) ($validated['delivery'] ?? 0);
            $totalAmount = $labor + $materials + $delivery;

            \App\Models\ServiceDetail::updateOrCreate(
                ['report_id' => $report->id],
                [
                    'labor' => $labor,
                    'parts_total_charge' => $materials,
                    'pullout_delivery' => $delivery,
                    'total_amount' => $totalAmount,
                ]
            );
        }

        $remainingBefore = max(0, $totalAmount - $alreadyPaid);

        // Amount paid in this payment event
        $amountPaid = (float) ($validated['amount_paid']
            ?? $validated['partial_payment_amount']
            ?? 0);

        if ($validated['payment_status'] === 'Paid') {
            $amountPaid = $remainingBefore > 0 ? $remainingBefore : $totalAmount;
        } elseif ($validated['payment_status'] === 'Unpaid') {
            $amountPaid = 0;
        } else {
            // Partial
            if ($amountPaid <= 0) {
                return back()->withInput()->with('error', 'Enter the amount paid for this partial payment.');
            }
            if ($amountPaid >= $remainingBefore && $remainingBefore > 0) {
                $validated['payment_status'] = 'Paid';
                $amountPaid = $remainingBefore;
            }
        }

        if ($amountPaid > $remainingBefore + 0.0001) {
            return back()->withInput()->with('error', 'Amount paid cannot exceed the remaining balance of ₱' . number_format($remainingBefore, 2) . '.');
        }

        $paymentDate = $validated['payment_date'] ?? null;
        if (in_array($validated['payment_status'], ['Paid', 'Partial']) && $amountPaid > 0) {
            $paymentDate = $paymentDate ? \Carbon\Carbon::parse($paymentDate) : now();
        }

        $transaction = \App\Models\Transaction::create([
            'report_id' => $report->id,
            'parts_total' => $materials,
            'labor_total' => $labor,
            'total_amount' => $totalAmount,
            'payment_status' => $validated['payment_status'],
            'payment_method' => $validated['payment_method'] ?? null,
            'amount_paid' => $amountPaid,
            'partial_payment_amount' => $validated['payment_status'] === 'Partial' ? $amountPaid : null,
            'reference_no' => $validated['reference_no'] ?? null,
            'receipt_no' => \App\Models\Transaction::generateReceiptNo(),
            'payment_date' => $paymentDate,
            'payment_due' => $validated['payment_due'] ?? null,
            'received_by' => $validated['received_by'] ?? (auth()->user() ? auth()->user()->first_name . ' ' . auth()->user()->last_name : 'System'),
        ]);

        $remainingAfter = max(0, $totalAmount - ($alreadyPaid + $amountPaid));

        // PayMongo link for remaining balance
        $paymongoSecret = env('PAYMONGO_SECRET_KEY');
        if ($validated['payment_status'] !== 'Paid' && $remainingAfter >= 100 && !empty($paymongoSecret)) {
            try {
                $response = Http::withBasicAuth($paymongoSecret, '')
                    ->withHeaders([
                        'accept' => 'application/json',
                        'content-type' => 'application/json',
                    ])
                    ->post('https://api.paymongo.com/v1/links', [
                        'data' => [
                            'attributes' => [
                                'amount' => intval($remainingAfter * 100),
                                'description' => 'Repair Service Payment for Report #' . $report->id,
                                'remarks' => 'Transaction #' . $transaction->id,
                            ],
                        ],
                    ]);

                if ($response->successful()) {
                    $paymongoData = $response->json()['data'];
                    $transaction->update([
                        'paymongo_link_id' => $paymongoData['id'],
                        'payment_url' => $paymongoData['attributes']['checkout_url'],
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('PayMongo Link Creation Failed: ' . $e->getMessage());
            }
        }

        $this->applyWarrantyIfPaid($transaction);

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Payment recorded successfully.' . ($remainingAfter > 0
                ? ' Remaining balance: ₱' . number_format($remainingAfter, 2) . '.'
                : ' Fully paid.'));
    }

    public function paymongoWebhook(Request $request)
    {
        $payload = $request->all();

        if (isset($payload['data']['type']) && $payload['data']['type'] === 'event' && $payload['data']['attributes']['type'] === 'link.payment.paid') {
            $linkId = $payload['data']['attributes']['data']['attributes']['link_id'] ?? null;

            if ($linkId) {
                $transaction = \App\Models\Transaction::where('paymongo_link_id', $linkId)->first();

                if ($transaction && $transaction->payment_status !== 'Paid') {
                    $remaining = max(
                        0,
                        (float) $transaction->total_amount - \App\Models\Transaction::totalPaidForReport($transaction->report_id, $transaction->id)
                    );

                    $transaction->update([
                        'payment_status' => 'Paid',
                        'amount_paid' => $remaining > 0 ? $remaining : $transaction->total_amount,
                        'payment_date' => now(),
                    ]);

                    $this->applyWarrantyIfPaid($transaction);

                    \Log::info("Webhook Success: Transaction #{$transaction->id} automatically marked as Paid.");
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function show(\App\Models\Transaction $transaction)
    {
        $this->checkTransactionAccess();
        $transaction->load(['report.customer.serviceReports', 'report.details', 'report.transactions', 'report.appliance']);

        $billTotal = (float) ($transaction->total_amount ?? 0);
        $alreadyPaid = \App\Models\Transaction::totalPaidForReport($transaction->report_id);
        $remaining = max(0, $billTotal - $alreadyPaid);

        return view('transactions.show', compact('transaction', 'billTotal', 'alreadyPaid', 'remaining'));
    }

    public function edit(\App\Models\Transaction $transaction)
    {
        $this->checkTransactionAccess();

        if ($transaction->isLocked()) {
            return redirect()
                ->route('transactions.show', $transaction)
                ->with('error', 'This payment cannot be edited. Add a new payment for any remaining balance.');
        }

        return view('transactions.edit', compact('transaction'));
    }

    public function update(Request $request, \App\Models\Transaction $transaction)
    {
        $this->checkTransactionAccess();

        if ($transaction->isLocked()) {
            return redirect()
                ->route('transactions.show', $transaction)
                ->with('error', 'This payment cannot be edited. Add a new payment for any remaining balance.');
        }

        $validated = $request->validate([
            'total_amount' => 'numeric',
            'payment_status' => 'string|in:Paid,Unpaid,Partial',
            'payment_method' => 'nullable|string',
            'amount_paid' => 'nullable|numeric|min:0',
            'partial_payment_amount' => 'required_if:payment_status,Partial|nullable|numeric|min:0',
            'reference_no' => 'nullable|string',
            'received_by' => 'nullable|string',
            'payment_date' => 'nullable|date',
            'payment_due' => 'nullable|date',
        ]);

        $billTotal = (float) ($validated['total_amount'] ?? $transaction->total_amount);
        $alreadyPaidOthers = \App\Models\Transaction::totalPaidForReport($transaction->report_id, $transaction->id);
        $remainingBefore = max(0, $billTotal - $alreadyPaidOthers);

        $amountPaid = (float) ($validated['amount_paid']
            ?? $validated['partial_payment_amount']
            ?? $transaction->amountPaidThisPayment());

        if (($validated['payment_status'] ?? $transaction->payment_status) === 'Paid') {
            $amountPaid = $remainingBefore;
            $validated['payment_status'] = 'Paid';
        } elseif (($validated['payment_status'] ?? '') === 'Unpaid') {
            $amountPaid = 0;
        } elseif (($validated['payment_status'] ?? '') === 'Partial') {
            if ($amountPaid >= $remainingBefore && $remainingBefore > 0) {
                $validated['payment_status'] = 'Paid';
                $amountPaid = $remainingBefore;
            }
        }

        if ($amountPaid > $remainingBefore + 0.0001) {
            return back()->withInput()->with('error', 'Amount paid cannot exceed the remaining balance of ₱' . number_format($remainingBefore, 2) . '.');
        }

        $validated['amount_paid'] = $amountPaid;
        $validated['partial_payment_amount'] = ($validated['payment_status'] ?? '') === 'Partial' ? $amountPaid : null;
        $validated['total_amount'] = $billTotal;

        if (($validated['payment_status'] ?? '') === 'Paid' && empty($validated['payment_date']) && !$transaction->payment_date) {
            $validated['payment_date'] = now();
        }

        $transaction->update($validated);
        $this->applyWarrantyIfPaid($transaction->fresh());

        return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully.');
    }

    public function destroy(\App\Models\Transaction $transaction)
    {
        $this->checkTransactionAccess();

        if ($transaction->isLocked() && auth()->user()->role !== 'Administrator') {
            return redirect()
                ->route('transactions.index')
                ->with('error', 'Fully paid transactions cannot be deleted by cashiers.');
        }

        $transaction->forceFill(['deleted_by' => auth()->id()])->save();
        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully.');
    }

    public function receipt(\App\Models\Transaction $transaction)
    {
        $this->checkTransactionAccess();
        $transaction->load(['report.customer', 'report.appliance', 'report.details', 'report.transactions']);

        $billTotal = (float) $transaction->total_amount;
        $paymentHistory = $transaction->report
            ? $transaction->report->transactions->sortBy('created_at')
            : collect([$transaction]);
        $totalPaid = $paymentHistory->sum(fn ($t) => $t->amountPaidThisPayment());
        $remaining = max(0, $billTotal - $totalPaid);

        return view('transactions.receipt', compact('transaction', 'billTotal', 'paymentHistory', 'totalPaid', 'remaining'));
    }
}
