<?php

use App\Http\Controllers\Central\AuthController;
use App\Http\Controllers\Central\TenantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Central)
|--------------------------------------------------------------------------
|
| These routes are NOT tenant-aware and run on central database.
| For tenant-specific API routes, use routes/tenant.php instead.
|
*/

// Health check endpoint
Route::group(['prefix' => 'central'], function () {
    Route::get('/ping', function () {
        return response()->json(['status' => 'ok']);
    });

    Route::post('/register', [AuthController::class, 'register']);

    Route::get('tenants',          [TenantController::class, 'index']);
    Route::post('tenants',         [TenantController::class, 'store']);
    Route::get('tenants/{id}',     [TenantController::class, 'show']);
    Route::delete('tenants/{id}',  [TenantController::class, 'destroy']);
});
