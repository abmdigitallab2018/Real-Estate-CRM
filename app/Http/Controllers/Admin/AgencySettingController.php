<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AgencySettingController extends Controller
{
    /**
     * Display agency settings view.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $auditLogs = AuditLog::with('user')->latest()->take(20)->get();

        return Inertia::render('Admin/Settings/Index', [
            'tenant' => $tenant,
            'auditLogs' => $auditLogs,
        ]);
    }

    /**
     * Update agency settings.
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return back()->with('error', 'No active tenant agency found.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'currency' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:10',
            'logo_url' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);

        $tenant->update($validated);

        AuditLog::record('Agency Settings Updated', $tenant);

        return back()->with('success', 'Agency settings updated successfully.');
    }
}
