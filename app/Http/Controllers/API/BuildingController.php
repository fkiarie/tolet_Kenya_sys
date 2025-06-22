<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BuildingController extends Controller
{
    // Get a paginated list of buildings with landlord and units
    public function index()
    {
        return Building::with(['landlord', 'units'])->paginate(15);
    }

    //  Store a new building
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'county' => 'required|string|max:255',
            'constituency' => 'required|string|max:255',
            'location' => 'required|string',
            'unit_type' => 'required|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'landlord_id' => 'required|exists:landlords,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('buildings', 'public');
        }

        $building = Building::create($validated);

        return response()->json($building, 201);
    }

    // Show a single building with landlord and nested unit-tenant relationships
    public function show(Building $building)
    {
        return $building->load(['landlord', 'units.tenants']);
    }

    // Update an existing building
    public function update(Request $request, Building $building)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'county' => 'sometimes|string|max:255',
            'constituency' => 'sometimes|string|max:255',
            'location' => 'sometimes|string',
            'unit_type' => 'sometimes|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'landlord_id' => 'sometimes|exists:landlords,id',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($building->image) {
                Storage::disk('public')->delete($building->image);
            }
            $validated['image'] = $request->file('image')->store('buildings', 'public');
        }

        $building->update($validated);

        return response()->json($building, 200);
    }

    // Delete a building
    public function destroy(Building $building)
    {
        // Delete image if exists
        if ($building->image) {
            Storage::disk('public')->delete($building->image);
        }

        $building->delete();

        return response()->json(['message' => 'Building deleted successfully.'], 200);
    }

    //  List all units under a building -- show status. 
    public function units(Building $building)
    {
        return $building->units()->with('tenants')->get();
    }
}
