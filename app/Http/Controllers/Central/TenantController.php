<?php
// app/Http/Controllers/Api/TenantController.php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TenantController extends Controller
{
    /**
     * Create a new tenant with a domain.
     */
    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'company_name' => 'required|string|max:255',
                'domain'    => 'required|string|max:255|unique:domains,domain',
                'name'   => 'required|string|max:255',
                'email'  => 'required|string|email|max:255|unique:users,email',
                'address' => 'nullable|string|max:255',
            ]);

        
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make('password'), 
            ]);

            // dd($user);

            // Auto-generate an ID if not provided
            $tenantId = $validated['domain'] ?? Str::slug($validated['domain']);
            $domain = $validated['domain'].'.localhost';

            $tenant = Tenant::create([
                'id' => $tenantId,
                'owner_id' => $user->id,
                'company_name' => $validated['company_name'],
                'address' => $request->input('address', null),
            ]);
            $tenant->domains()->create(['domain' => $domain]);

            return response()->json([
                'message' => 'Tenant created successfully.',
                'tenant'  => [
                    'id'      => $tenant->id,
                    'domain'  => $domain,
                    'data'    => $tenant->data,
                ],
            ], 201);
        }catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * List all tenants.
     */
    public function index()
    {
        $tenants = Tenant::with('domains')->get()->map(function ($tenant) {
            return [
                'id'      => $tenant->id,
                'domains' => $tenant->domains->pluck('domain'),
                'data'    => $tenant->data,
            ];
        });

        return response()->json(['tenants' => $tenants]);
    }

    /**
     * Show a single tenant.
     */
    public function show(string $id)
    {
        $tenant = Tenant::with('domains')->findOrFail($id);

        return response()->json([
            'id'      => $tenant->id,
            'domains' => $tenant->domains->pluck('domain'),
            'data'    => $tenant->data,
        ]);
    }

    /**
     * Delete a tenant.
     */
    public function destroy(string $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->delete(); // Also drops the tenant DB if configured

        return response()->json(['message' => 'Tenant deleted successfully.']);
    }
}