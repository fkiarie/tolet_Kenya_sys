<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{
    LandlordController,
    BuildingController,
    UnitController,
    TenantController
};

use App\Http\Controllers\API\AuthController;

Route::post('login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    // All routes within this group require authentication via Sanctum
    // Resource routes
    Route::apiResource('landlords', LandlordController::class);
    Route::apiResource('buildings', BuildingController::class);
    Route::apiResource('units', UnitController::class);
    Route::apiResource('tenants', TenantController::class);

    // Custom routes for additional functionality not covered by standard resource routes
    Route::get('buildings/{building}/units', [BuildingController::class, 'units']);
    Route::get('units/available', [UnitController::class, 'available']);
    Route::post('tenants/{tenant}/assign-unit/{unit}', [TenantController::class, 'assignUnit']);
    Route::delete('tenants/{tenant}/remove-unit/{unit}', [TenantController::class, 'removeUnit']);
});
