<?php

namespace App\Http\Controllers;

use App\Models\CommonProblem;
use Illuminate\Http\Request;

class CommonProblemController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'problem_name' => 'required|string|max:255',
            'appliance_type_id' => 'required|exists:appliance_types,id',
        ]);

        $problem = CommonProblem::create([
            'problem_name' => $validated['problem_name'],
            'appliance_type_id' => $validated['appliance_type_id'],
        ]);

        return response()->json([
            'success' => true,
            'problem' => $problem,
        ]);
    }
}
