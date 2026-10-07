<?php

namespace App\Http\Controllers;

use App\Models\ApplianceType;
use Illuminate\Http\Request;

class ApplianceTypeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:appliance_types,name',
        ]);

        $applianceType = ApplianceType::create([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'success' => true,
            'applianceType' => $applianceType,
        ]);
    }
}
