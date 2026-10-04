<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    private function checkCustomerAccess()
    {
        if (auth()->check() && !in_array(auth()->user()->role, ['Administrator', 'Secretary'])) {
            abort(403, 'Unauthorized. Only Secretaries and Administrators can manage Customers.');
        }
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $hasAppliances = $request->input('has_appliances');

        $customers = \App\Models\Customer::with(['appliances', 'serviceReports'])
            ->when($search, function ($q) use ($search) {
            $q->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%$search%")
                        ->orWhere('last_name', 'like', "%$search%")
                        ->orWhere('phone_no', 'like', "%$search%");
                }
                );
            })
            ->when($hasAppliances, function ($q) use ($hasAppliances) {
            if ($hasAppliances === 'yes') {
                $q->has('appliances');
            }
            elseif ($hasAppliances === 'no') {
                $q->doesntHave('appliances');
            }
        })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search', 'hasAppliances'));
    }

    public function create()
    {
        $this->checkCustomerAccess();
        $applianceTypes = \App\Models\ApplianceType::with('commonProblems')->get();
        return view('customers.create', compact('applianceTypes'));
    }

    public function store(Request $request)
    {
        $this->checkCustomerAccess();
        $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => [
                'nullable', 'string', 'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = \App\Models\Customer::where('first_name', $request->first_name)
                        ->where('last_name', $value)
                        ->exists();
                    if ($exists) {
                        $fail('A customer with the same first and last name already exists.');
                    }
                },
            ],
            'address' => 'nullable|string',
            'phone_no' => 'required|numeric|digits_between:7,15',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'appliance_type_id' => 'nullable|exists:appliance_types,id',
            'appliance_product' => 'nullable|string',
            'appliance_brand' => 'nullable|string',
            'appliance_model' => 'nullable|string',
            'appliance_serial' => 'nullable|string',
            'appliance_dealer' => 'nullable|string|max:255',
            'appliance_purchase_date' => 'nullable|date',
            'appliance_warranty' => 'nullable|date',
            'common_problems' => 'nullable|array',
            'common_problems.*' => 'exists:common_problems,id',
            'other_problem' => 'nullable|string',
        ], [
            'phone_no.required' => 'The phone number is required.',
            'phone_no.numeric' => 'The phone number must contain only numbers.',
            'phone_no.digits_between' => 'The phone number must be between 7 and 15 digits.',
        ]);

        $data = $request->except('profile_picture', 'appliance_type_id', 'appliance_product', 'appliance_brand', 'appliance_model', 'appliance_serial', 'appliance_dealer', 'appliance_purchase_date', 'appliance_warranty', 'common_problems', 'other_problem');

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('customer-profiles', 'public');
            $data['profile_picture'] = 'storage/' . $path;
        }

        $customer = \App\Models\Customer::create($data);

        // Create appliance if data is provided
        if ($request->filled('appliance_product') || $request->filled('appliance_brand') || $request->filled('appliance_type_id')) {
            $applianceType = \App\Models\ApplianceType::find($request->appliance_type_id);
            $appliance = \App\Models\Appliance::create([
                'customer_id' => $customer->id,
                'category' => $applianceType ? $applianceType->name : null,
                'product' => $request->appliance_product,
                'brand' => $request->appliance_brand,
                'model_no' => $request->appliance_model,
                'serial_no' => $request->appliance_serial,
                'dealer' => $request->appliance_dealer,
                'date_in' => $request->appliance_purchase_date,
                'warranty_end' => $request->appliance_warranty,
            ]);

            // Create appliance problems
            if ($request->filled('common_problems')) {
                foreach ($request->common_problems as $problemId) {
                    \App\Models\ApplianceProblem::create([
                        'appliance_id' => $appliance->id,
                        'common_problem_id' => $problemId,
                    ]);
                }
            }

            // Add other problem if provided
            if ($request->filled('other_problem')) {
                \App\Models\ApplianceProblem::create([
                    'appliance_id' => $appliance->id,
                    'other_problem' => $request->other_problem,
                ]);
            }
        }

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(\App\Models\Customer $customer)
    {
        $customer->load(['appliances', 'serviceReports' => function ($q) {
            $q->latest()->with('appliance');
        }]);
        return view('customers.show', compact('customer'));
    }

    public function edit(\App\Models\Customer $customer)
    {
        $this->checkCustomerAccess();
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, \App\Models\Customer $customer)
    {
        $this->checkCustomerAccess();
        $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => [
                'nullable', 'string', 'max:255',
                function ($attribute, $value, $fail) use ($request, $customer) {
                    $exists = \App\Models\Customer::where('first_name', $request->first_name)
                        ->where('last_name', $value)
                        ->where('id', '!=', $customer->id)
                        ->exists();
                    if ($exists) {
                        $fail('A customer with the same first and last name already exists.');
                    }
                },
            ],
            'address' => 'nullable|string',
            'phone_no' => 'required|numeric|digits_between:7,15',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'phone_no.required' => 'The phone number is required.',
            'phone_no.numeric' => 'The phone number must contain only numbers.',
            'phone_no.digits_between' => 'The phone number must be between 7 and 15 digits.',
        ]);

        $data = $request->except('profile_picture');

        if ($request->hasFile('profile_picture')) {
            if ($customer->profile_picture && !str_starts_with($customer->profile_picture, 'http')) {
                $oldPath = str_replace('storage/', '', $customer->profile_picture);
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('profile_picture')->store('customer-profiles', 'public');
            $data['profile_picture'] = 'storage/' . $path;
        }

        $customer->update($data);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(\App\Models\Customer $customer)
    {
        $this->checkCustomerAccess();
        // Bypass $fillable: deleted_by should not be user-input controlled.
        $customer->forceFill(['deleted_by' => auth()->id()])->save();
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
