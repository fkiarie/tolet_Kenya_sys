<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Landlord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandlordController extends Controller
{
    // List all landlords
    public function index()
    {
        return Landlord::with('buildings')->paginate(15);
    }

    // Store a new landlord
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'national_id' => 'required|string|max:50|unique:landlords,national_id',
            'phone_number' => 'required|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('landlords', 'public');
        }

        $landlord = Landlord::create($validated);

        return response()->json($landlord, 201);
    }

    // Get a specific landlord
    public function show(Landlord $landlord)
    {
        return $landlord->load('buildings');
    }

    // Update a landlord
    public function update(Request $request, Landlord $landlord)
    {
        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'national_id' => 'sometimes|string|max:50|unique:landlords,national_id,' . $landlord->id,
            'phone_number' => 'sometimes|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($landlord->photo) {
                Storage::disk('public')->delete($landlord->photo);
            }
            $validated['photo'] = $request->file('photo')->store('landlords', 'public');
        }

        $landlord->update($validated);

        return response()->json($landlord);
    }

    // Delete a landlord
    public function destroy(Landlord $landlord)
    {
        if ($landlord->photo) {
            Storage::disk('public')->delete($landlord->photo);
        }

        $landlord->delete();

        return response()->json(['message' => 'Landlord deleted successfully.'], 200);
    }
}
