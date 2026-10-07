<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ServiceDetail;
use App\Notifications\ServiceReportCreated;
use App\Notifications\ServiceReportUpdated;
use Illuminate\Support\Facades\Notification;

class ServiceReportController extends Controller
{
    private function getTechniciansWithAvailability()
    {
        $technicians = User::where('role', 'Technician')->get();

        // Mark technicians with any non-closed service as "Busy". Use account status "Inactive" as "Off-Duty".
        $busyNames = ServiceDetail::query()
            ->whereNotNull('technician')
            ->where('technician', '<>', '')
            ->whereHas('report', function ($q) {
                $q->whereNotIn('status', ['Completed', 'Cancelled', 'Canceled']);
            })
            ->pluck('technician')
            ->flatMap(function ($list) {
                return collect(explode(',', (string) $list))
                    ->map(fn($name) => trim($name))
                    ->filter();
            })
            ->map(fn($name) => strtolower($name))
            ->unique()
            ->values()
            ->all();

        $busyLookup = array_fill_keys($busyNames, true);

        foreach ($technicians as $tech) {
            $fullName = trim(($tech->first_name ?? '') . ' ' . ($tech->last_name ?? ''));
            $fullNameKey = strtolower($fullName);

            if (strtolower((string) $tech->status) === 'inactive') {
                $availability = 'Off-Duty';
            } elseif ($fullName !== '' && isset($busyLookup[$fullNameKey])) {
                $availability = 'Busy';
            } else {
                $availability = 'Available';
            }

            $tech->setAttribute('availability_status', $availability);
        }

        return $technicians;
    }

    private function checkServiceCreationAccess()
    {
        if (auth()->check() && !in_array(auth()->user()->role, ['Administrator', 'Secretary'])) {
            abort(403, 'Unauthorized. Only Secretaries and Administrators can create or delete Service Reports.');
        }
    }

    private function processParts(\App\Models\ServiceReport $report, $partsInput, $isNew = false)
    {
        if ($partsInput === null)
            return 0;

        $partsData = [];
        $partsTotalCost = 0;

        // Restore old stock if updating before wiping the pivot
        if (!$isNew) {
            foreach ($report->parts as $oldPart) {
                // Return stock visually back to inventory
                $oldPart->increment('quantity_stock', $oldPart->pivot->quantity);
            }
        }

        if (is_array($partsInput)) {
            foreach ($partsInput as $partItem) {
                if (isset($partItem['id']) && isset($partItem['quantity'])) {
                    $qty = (int) $partItem['quantity'];
                    $price = (float) str_replace(['₱', ','], '', $partItem['price']);

                    // Check stock availability before deducting
                    $actualPart = \App\Models\Part::find($partItem['id']);
                    if ($actualPart) {
                        if ($actualPart->quantity_stock < $qty) {
                            // Restore old stock since we're aborting
                            if (!$isNew) {
                                foreach ($report->parts as $oldPart) {
                                    $oldPart->decrement('quantity_stock', $oldPart->pivot->quantity);
                                }
                            }
                            $message = $actualPart->quantity_stock === 0
                                ? "{$actualPart->name} is currently out of stock."
                                : "{$actualPart->name} has only {$actualPart->quantity_stock} in stock. You requested {$qty}.";
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'parts' => $message
                            ]);
                        }

                        $partsData[$partItem['id']] = [
                            'quantity' => $qty,
                            'price' => $price,
                        ];
                        $partsTotalCost += ($qty * $price);

                        // Deduct new stock
                        $actualPart->decrement('quantity_stock', $qty);
                    }
                }
            }
        }

        // Sync the pivot table with quantities & prices
        $report->parts()->sync($partsData);
        return $partsTotalCost;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $date = $request->input('date');

        $userRole = auth()->user()->role;
        $technicianName = trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? ''));

        $services = \App\Models\ServiceReport::with(['customer', 'appliance', 'details'])
            ->when($userRole === 'Technician', function ($q) use ($technicianName) {
                $q->whereHas('details', function($query) use ($technicianName) {
                    $query->where('technician', 'like', "%{$technicianName}%");
                });
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('customer_name', 'like', "%$search%")
                          ->orWhere('id', 'like', "%$search%");
                });
            })
            ->when($status, function ($q) use ($status) {
                if ($status === 'In Progress') {
                    $q->whereIn('status', ['Waiting for Parts', 'Under Repair']);
                } else {
                    $q->where('status', $status);
                }
            })
            ->when($date, function ($q) use ($date) {
                $q->whereDate('date_in', $date);
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('services.index', compact('services', 'search', 'status', 'date'));
    }

    public function create()
    {
        $this->checkServiceCreationAccess();
        $customers = \App\Models\Customer::all();
        $technicians = $this->getTechniciansWithAvailability();
        $applianceTypes = \App\Models\ApplianceType::with('commonProblems')->get();
        return view('services.create', compact('customers', 'technicians', 'applianceTypes'));
    }

    public function store(Request $request)
    {
        $this->checkServiceCreationAccess();
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'appliance_type_id' => 'required',
            'other_appliance_type' => 'required_if:appliance_type_id,other|nullable|string',
            'appliance_brand' => 'nullable|string',
            'appliance_model' => 'nullable|string',
            'appliance_serial' => 'nullable|string',
            'dealer' => 'nullable|string',
            'dop' => 'nullable|date',
            'warranty_end' => 'nullable|date',
            'date_in' => 'required|date',
            'status' => 'required|string',
            'problem_desc' => 'nullable|string',
            'common_problems' => 'nullable|array',
            'common_problems.*' => 'exists:common_problems,id',
            'notes' => 'nullable|string',
            'technicians' => 'nullable|array|max:3',
        ]);

        $customer = \App\Models\Customer::find($validated['customer_id']);

        // Determine appliance type name
        $applianceTypeName = null;
        if ($validated['appliance_type_id'] === 'other') {
            $applianceTypeName = $validated['other_appliance_type'];
        } elseif ($validated['appliance_type_id']) {
            $applianceType = \App\Models\ApplianceType::find($validated['appliance_type_id']);
            $applianceTypeName = $applianceType ? $applianceType->name : null;
        }

        // Create appliance
        $appliance = \App\Models\Appliance::create([
            'customer_id' => $customer->id,
            'product' => $applianceTypeName,
            'brand' => $validated['appliance_brand'] ?? null,
            'model_no' => $validated['appliance_model'] ?? null,
            'serial_no' => $validated['appliance_serial'] ?? null,
            'dealer' => $validated['dealer'] ?? null,
            'date_in' => $validated['dop'] ?? null,
            'warranty_end' => $validated['warranty_end'] ?? null,
        ]);

        // Create appliance problems if provided
        if (isset($validated['common_problems']) && is_array($validated['common_problems'])) {
            foreach ($validated['common_problems'] as $problemId) {
                \App\Models\ApplianceProblem::create([
                    'appliance_id' => $appliance->id,
                    'common_problem_id' => $problemId,
                ]);
            }
        }

        // Problem description = the notes typed on the create form
        $problemDesc = trim($validated['notes'] ?? '');

        // Create service report
        $report = \App\Models\ServiceReport::create([
            'customer_id' => $customer->id,
            'appliance_id' => $appliance->id,
            'date_in' => $validated['date_in'],
            'status' => $validated['status'],
            'customer_name' => trim($customer->first_name . ' ' . $customer->last_name),
        ]);

        // Create initial Service Detail
        $techs = isset($validated['technicians']) ? implode(', ', $validated['technicians']) : null;

        ServiceDetail::create([
            'report_id' => $report->id,
            'service_types' => [],
            'labor' => 0,
            'parts_total_charge' => 0,
            'miscellaneous_cost' => 0,
            'total_amount' => 0,
            'complaint' => $problemDesc,
            'technician' => $techs,
        ]);

        return redirect()->route('services.show', $report)->with('success', 'Service Report created successfully.');
    }

    public function show(\App\Models\ServiceReport $service)
    {
        $service->load(['comments.user', 'transactions', 'appliance.problems.commonProblem']);
        $technicians = $this->getTechniciansWithAvailability();
        $techStatusMap = $technicians->mapWithKeys(function (User $tech) {
            $name = strtolower(trim(($tech->first_name ?? '') . ' ' . ($tech->last_name ?? '')));
            return [$name => $tech->availability_status];
        });

        return view('services.show', compact('service', 'techStatusMap'));
    }

    public function edit(\App\Models\ServiceReport $service)
    {
        if (auth()->check() && auth()->user()->role === 'Cashier') {
            abort(403, 'Cashiers cannot edit Service Reports.');
        }
        $customers = \App\Models\Customer::with('appliances')->get();
        $technicians = $this->getTechniciansWithAvailability();
        $parts = \App\Models\Part::all();
        $servicePrices = \App\Models\ServicePrice::query()
            ->orderBy('service_name')
            ->get()
            ->unique('service_name')
            ->values();
        $service->load(['parts', 'appliance.problems.commonProblem']);
        return view('services.edit', compact('service', 'customers', 'technicians', 'parts', 'servicePrices'));
    }

    public function update(Request $request, \App\Models\ServiceReport $service)
    {
        if (auth()->user()->role === 'Cashier') {
            abort(403, 'Cashiers cannot edit Service Reports.');
        }

        $userRole = auth()->user()->role;
        $rules = [
            'customer_id' => 'required|exists:customers,id',
            'appliance_id' => 'required|exists:appliances,id',
            'date_in' => 'required|date',
            'status' => 'required|string',
            'problem_desc' => 'required|string',
            'dealer' => 'nullable|string',
            'dop' => 'nullable|date',
            'technicians' => 'nullable|array|max:3',
            'service_types' => 'nullable|array',
            'custom_services' => 'nullable|array',
            'custom_services.*.name' => 'nullable|string|max:255',
            'custom_services.*.price' => 'nullable|numeric|min:0',
            'findings' => 'nullable|string',
            'remarks' => 'nullable|string',
            'parts' => 'nullable|array',
            'miscellaneous_cost' => 'nullable|numeric',
            'labor_cost' => 'nullable|numeric',
        ];

        if ($userRole === 'Technician') {
            $ignores = ['customer_id', 'appliance_id', 'date_in', 'problem_desc', 'dealer', 'dop', 'technicians'];
            foreach ($ignores as $ignore)
                unset($rules[$ignore]);
        } elseif ($userRole === 'Secretary') {
            $ignores = ['service_types', 'custom_services', 'findings', 'remarks', 'parts', 'miscellaneous_cost', 'labor_cost'];
            foreach ($ignores as $ignore)
                unset($rules[$ignore]);
        }

        $validated = $request->validate($rules);

        // Preserve missing attributes for disabled HTML form fields
        if ($userRole === 'Technician') {
            $validated['customer_id'] = $service->customer_id;
            $validated['appliance_id'] = $service->appliance_id;
            $validated['date_in'] = $service->date_in;
            $validated['problem_desc'] = $service->details ? $service->details->complaint : '';
            // dealer and dop are stored in appliance, not service_report - technicians cannot edit them
            $validated['technicians'] = $service->details ? explode(', ', $service->details->technician) : [];
        } elseif ($userRole === 'Secretary') {
            $validated['service_types'] = $service->details ? $service->details->service_types : [];
            $validated['custom_services'] = $service->details ? ($service->details->custom_services ?? []) : [];
            $validated['findings'] = $service->findings ?? '';
            $validated['remarks'] = $service->remarks ?? '';
            $validated['labor_cost'] = $service->details ? $service->details->labor : 0;
            $validated['miscellaneous_cost'] = $service->details ? $service->details->miscellaneous_cost : 0;
        }

        // Merge catalog + custom service names into service_types for display/history
        $customServices = collect($validated['custom_services'] ?? [])
            ->filter(fn ($row) => filled($row['name'] ?? null))
            ->map(fn ($row) => [
                'name' => trim($row['name']),
                'price' => (float) ($row['price'] ?? 0),
            ])
            ->values()
            ->all();

        $catalogTypes = collect($validated['service_types'] ?? [])
            ->filter()
            ->map(fn ($n) => trim($n))
            ->values()
            ->all();

        $validated['service_types'] = collect($catalogTypes)
            ->merge(collect($customServices)->pluck('name'))
            ->unique()
            ->values()
            ->all();
        $validated['custom_services'] = $customServices;

        $customer = \App\Models\Customer::find($validated['customer_id']);
        $validated['customer_name'] = trim($customer->first_name . ' ' . $customer->last_name);

        // Save dealer and dop to appliance where they are stored
        if ($service->appliance) {
            $applianceData = [];
            if (isset($validated['dealer'])) {
                $applianceData['dealer'] = $validated['dealer'];
                unset($validated['dealer']);
            }
            if (isset($validated['dop'])) {
                $applianceData['date_in'] = $validated['dop'];
                unset($validated['dop']);
            }
            if (!empty($applianceData)) {
                $service->appliance->update($applianceData);
            }
        }


        // Remove problem_desc from validated array before updating ServiceReport
        // (it's stored in service_details.complaint, not service_reports)
        unset($validated['problem_desc']);

        $service->update($validated);

        // Process Parts & Inventory Sync (only for technician or admin)
        $partsTotalCost = $service->details ? $service->details->parts_total_charge : 0;
        if ($userRole !== 'Cashier') {
            $partsInput = $request->input('parts', []);
            $partsTotalCost = $this->processParts($service, $partsInput, false);
        }

        // Update or Create ServiceDetail
        $techs = isset($validated['technicians']) ? implode(', ', $validated['technicians']) : null;
        if ($userRole === 'Secretary') {
            $labor = $validated['labor_cost'] ?? ($service->details->labor ?? 0);
            $miscCost = $validated['miscellaneous_cost'] ?? ($service->details->miscellaneous_cost ?? 0);
        } else {
            $labor = $request->input('labor_cost', $service->details->labor ?? 0);
            $miscCost = $request->input('miscellaneous_cost', $service->details->miscellaneous_cost ?? 0);
        }
        $totalAmount = $labor + $partsTotalCost + $miscCost;

        ServiceDetail::updateOrCreate(
            ['report_id' => $service->id],
            [
                'complaint' => $service->details ? $service->details->complaint : $request->problem_desc,
                'labor' => $labor,
                'parts_total_charge' => $partsTotalCost,
                'miscellaneous_cost' => $miscCost,
                'service_types' => $validated['service_types'] ?? [],
                'custom_services' => $validated['custom_services'] ?? [],
                'total_amount' => $totalAmount,
                'technician' => $techs,
            ]
        );

        // Trigger Notification to all users
        $users = User::all();
        Notification::send($users, new ServiceReportUpdated($service->id, $service->customer_name));

        return redirect()->route('services.index')->with('success', 'Service Report updated successfully.');
    }

    public function destroy(\App\Models\ServiceReport $service)
    {
        $this->checkServiceCreationAccess();
        // Bypass $fillable: deleted_by should not be user-input controlled.
        $service->forceFill(['deleted_by' => auth()->id()])->save();
        $service->delete();
        return redirect()->route('services.index')->with('success', 'Service Report deleted successfully.');
    }

    public function storeComment(Request $request, \App\Models\ServiceReport $service)
    {
        $request->validate([
            'comment_text' => 'required|string',
        ]);

        \App\Models\ServiceProgressComment::create([
            'report_id' => $service->id,
            'comment_text' => $request->comment_text,
            'created_by' => auth()->id(),
            'created_by_name' => auth()->user()->full_name, // Assuming full_name exists on User
            'progress_key' => 'update', // Default key
        ]);

        return back()->with('success', 'Comment added successfully.');
    }

    public function print(\App\Models\ServiceReport $service)
    {
        $service->load(['details', 'appliance.problems.commonProblem', 'parts', 'transactions']);
        return view('services.print', compact('service'));
    }
}
