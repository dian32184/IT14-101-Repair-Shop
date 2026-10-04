<?php

namespace App\Http\Controllers;

use App\Models\Appliance;
use App\Models\Customer;
use Illuminate\Http\Request;

class ApplianceController extends Controller
{
    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'brand' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'product' => 'nullable|string|max:255', // Appliance Type
            'model_no' => [
                'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('appliances', 'model_no')->whereNull('deleted_at')
            ],
            'serial_no' => [
                'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('appliances', 'serial_no')->whereNull('deleted_at')
            ],
            'dealer' => 'nullable|string|max:255',
            'date_in' => 'nullable|date',
            'appliance_size' => 'nullable|in:Small,Medium,Large',
        ]);

        $validated['customer_id'] = $customer->id;

        Appliance::create($validated);

        return back()->with('success', 'Appliance added successfully.');
    }

    public function update(Request $request, Appliance $appliance)
    {
        $validated = $request->validate([
            'brand' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'product' => 'nullable|string|max:255', // Appliance Type
            'model_no' => [
                'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('appliances', 'model_no')->ignore($appliance->id)->whereNull('deleted_at')
            ],
            'serial_no' => [
                'nullable',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('appliances', 'serial_no')->ignore($appliance->id)->whereNull('deleted_at')
            ],
            'dealer' => 'nullable|string|max:255',
            'other_problem' => 'nullable|string|max:255',
            'date_in' => 'nullable|date',
            'appliance_size' => 'nullable|in:Small,Medium,Large',
        ]);

        $appliance->update($validated);

        // Handle other_problem - create or update appliance problem
        if ($request->filled('other_problem')) {
            // Check if there's already an "other" problem for this appliance
            $existingOtherProblem = \App\Models\ApplianceProblem::where('appliance_id', $appliance->id)
                ->whereNotNull('other_problem')
                ->first();

            if ($existingOtherProblem) {
                $existingOtherProblem->update(['other_problem' => $request->other_problem]);
            } else {
                \App\Models\ApplianceProblem::create([
                    'appliance_id' => $appliance->id,
                    'other_problem' => $request->other_problem,
                ]);
            }
        } else {
            // If other_problem is empty, remove existing other problems
            \App\Models\ApplianceProblem::where('appliance_id', $appliance->id)
                ->whereNotNull('other_problem')
                ->delete();
        }

        return back()->with('success', 'Appliance updated successfully.');
    }

    public function destroy(Appliance $appliance)
    {
        $appliance->delete();
        return back()->with('success', 'Appliance removed.');
    }
}
