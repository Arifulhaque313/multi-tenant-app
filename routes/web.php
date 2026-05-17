<?php

use App\Models\Tenant;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

// Route::middleware([
//     'auth:sanctum',
//     config('jetstream.auth_session'),
//     'verified',
// ])->group(function () {
//     Route::get('/dashboard', function () {
//         return Inertia::render('Dashboard');
//     })->name('dashboard');
// });

// routes/web.php, api.php or any other central route files you have

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        // Central routes that should be accessible on central domains
        Route::get('/', function () {
            return 'Welcome to the central application. This is not tenant-specific.';
        })->name('central.home');

        Route::get('/tenants', function () {
            // Example central route to list tenants
            $tenants = Tenant::get();
            return response()->json(['tenants' => $tenants]);
        })->name('central.tenants');

        // Add more central routes here as needed
    });
}
