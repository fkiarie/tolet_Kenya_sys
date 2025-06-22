<?php
// routes/api.php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\{LandlordController, BuildingController, UnitController, TenantController};
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    
    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::get('tokens', [AuthController::class, 'tokens']);
        Route::delete('revoke-token', [AuthController::class, 'revokeToken']);
    });
});

/*
|--------------------------------------------------------------------------
| Protected API Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    
    // Landlord routes
    Route::apiResource('landlords', LandlordController::class);
    
    // Building routes
    Route::apiResource('buildings', BuildingController::class);
    Route::get('buildings/{building}/units', [BuildingController::class, 'units']);
    
    // Unit routes
    Route::apiResource('units', UnitController::class);
    Route::get('units/available', [UnitController::class, 'available']);
    
    // Tenant routes
    Route::apiResource('tenants', TenantController::class);
    Route::post('tenants/{tenant}/assign-unit/{unit}', [TenantController::class, 'assignUnit']);
    Route::delete('tenants/{tenant}/remove-unit/{unit}', [TenantController::class, 'removeUnit']);
    
    // Dashboard/Statistics routes
    Route::get('dashboard/stats', function () {
        return response()->json([
            'total_buildings' => \App\Models\Building::count(),
            'total_units' => \App\Models\Unit::count(),
            'total_tenants' => \App\Models\Tenant::count(),
            'total_landlords' => \App\Models\Landlord::count(),
            'vacant_units' => \App\Models\Unit::where('state', 'vacant')->count(),
            'occupied_units' => \App\Models\Unit::where('state', 'occupied')->count(),
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->json([
        'message' => 'Route not found. Please check the API documentation.'
    ], 404);
});