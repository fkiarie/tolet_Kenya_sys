<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    // List all units
    public function index()
    {
        return Unit::with(['building', 'tenants'])->paginate(15);
    }

    // Store a new unit
    public function store(Request $request)
    {
        $validated = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'unit_no' => 'required|string|max:50',
            'state' => 'required|in:vacant,occupied,under_maintenance',
            'rent_per_month' => 'required|numeric',
            'deposit' => 'required|numeric',
            'unit_type' => 'required|in:bedsitter,studio,1_bedroom,2_bedroom,3_bedroom,shop,standalone',
        ]);

        $unit = Unit::create($validated);

        return response()->json($unit, 201);
    }

    //  Show a unit
    public function show(Unit $unit)
    {
        return $unit->load(['building', 'tenants']);
    }

    //  Update a unit
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'building_id' => 'sometimes|exists:buildings,id',
            'unit_no' => 'sometimes|string|max:50',
            'state' => 'sometimes|in:vacant,occupied,under_maintenance',
            'rent_per_month' => 'sometimes|numeric',
            'deposit' => 'sometimes|numeric',
            'unit_type' => 'sometimes|in:bedsitter,studio,1_bedroom,2_bedroom,3_bedroom,shop,standalone',
        ]);

        $unit->update($validated);

        return response()->json($unit);
    }

    //  Delete a unit
    public function destroy(Unit $unit)
    {
        $unit->delete();

        return response()->json(['message' => 'Unit deleted successfully.']);
    }

    //  List available units only
    public function available()
    {
        return Unit::available()->with('building')->get();
    }
}
