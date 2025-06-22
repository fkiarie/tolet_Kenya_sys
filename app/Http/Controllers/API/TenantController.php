<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenantController extends Controller
{
    // 🟢 List all tenants with units
    public function index()
    {
        return Tenant::with('units')->paginate(15);
    }

    // 🟢 Store a new tenant
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'national_id' => 'required|string|max:50|unique:tenants,national_id',
            'phone_number' => 'required|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'next_of_kin_name' => 'required|string|max:255',
            'next_of_kin_contact' => 'required|string|max:20',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('tenants', 'public');
        }

        $tenant = Tenant::create($validated);

        return response()->json($tenant, 201);
    }

    // 🟢 Show a tenant with units
    public function show(Tenant $tenant)
    {
        return $tenant->load('units');
    }

    // 🟢 Update tenant
    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'national_id' => 'sometimes|string|max:50|unique:tenants,national_id,' . $tenant->id,
            'phone_number' => 'sometimes|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'next_of_kin_name' => 'sometimes|string|max:255',
            'next_of_kin_contact' => 'sometimes|string|max:20',
        ]);

        if ($request->hasFile('photo')) {
            if ($tenant->photo) {
                Storage::disk('public')->delete($tenant->photo);
            }
            $validated['photo'] = $request->file('photo')->store('tenants', 'public');
        }

        $tenant->update($validated);

        return response()->json($tenant);
    }

    // 🟢 Delete tenant
    public function destroy(Tenant $tenant)
    {
        if ($tenant->photo) {
            Storage::disk('public')->delete($tenant->photo);
        }

        $tenant->delete();

        return response()->json(['message' => 'Tenant deleted successfully.']);
    }

    // ✅ Assign a unit to a tenant
    public function assignUnit(Request $request, Tenant $tenant, Unit $unit)
    {
        $validated = $request->validate([
            'move_in_date' => 'required|date',
        ]);

        $tenant->units()->attach($unit->id, [
            'move_in_date' => $validated['move_in_date'],
        ]);

        // Mark unit as occupied
        $unit->update(['state' => 'occupied']);

        return response()->json(['message' => 'Unit assigned to tenant.']);
    }

    // ✅ Remove a unit from a tenant
    public function removeUnit(Tenant $tenant, Unit $unit)
    {
        $tenant->units()->updateExistingPivot($unit->id, ['move_out_date' => now()]);
        $tenant->units()->detach($unit->id);

        // Mark unit as vacant
        $unit->update(['state' => 'vacant']);

        return response()->json(['message' => 'Unit removed from tenant.']);
    }
}
