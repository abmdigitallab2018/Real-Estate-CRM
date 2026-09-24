<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Deal;
use App\Models\Property;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminController extends Controller
{
    /**
     * Super Admin SaaS Platform Dashboard.
     */
    public function dashboard(): Response
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('status', 'active')->count();
        $trialTenants = Tenant::where('status', 'trial')->count();
        $suspendedTenants = Tenant::where('status', 'suspended')->count();

        $totalProperties = Property::withoutGlobalScope('tenant')->count();
        $totalDeals = Deal::withoutGlobalScope('tenant')->count();
        $totalUsers = User::withoutGlobalScope('tenant')->count();

        // Calculate Monthly Recurring Revenue (MRR)
        $mrr = Tenant::where('status', 'active')
            ->with('subscriptionPlan')
            ->get()
            ->sum(fn ($t) => $t->subscriptionPlan?->price ?? 0);

        $recentTenants = Tenant::with(['subscriptionPlan', 'users'])
            ->withCount(['properties', 'users'])
            ->latest()
            ->take(8)
            ->get();

        $plans = SubscriptionPlan::withCount('tenants')->get();

        return Inertia::render('SuperAdmin/Dashboard', [
            'stats' => [
                'total_tenants' => $totalTenants,
                'active_tenants' => $activeTenants,
                'trial_tenants' => $trialTenants,
                'suspended_tenants' => $suspendedTenants,
                'total_properties' => $totalProperties,
                'total_deals' => $totalDeals,
                'total_users' => $totalUsers,
                'mrr' => (float) $mrr,
            ],
            'recentTenants' => $recentTenants,
            'plans' => $plans,
        ]);
    }

    /**
     * Tenants management list.
     */
    public function tenants(Request $request): Response
    {
        $query = Tenant::with(['subscriptionPlan'])
            ->withCount(['properties', 'users', 'deals']);

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $tenants = $query->latest()->paginate(12)->withQueryString();
        $plans = SubscriptionPlan::where('is_active', true)->select('id', 'name', 'price')->get();

        return Inertia::render('SuperAdmin/Tenants', [
            'tenants' => $tenants,
            'plans' => $plans,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    /**
     * Onboard / create new tenant agency.
     */
    public function storeTenant(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:tenants,email',
            'phone' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        $slug = Str::slug($validated['name']);
        if (Tenant::where('slug', $slug)->exists()) {
            $slug .= '-' . rand(100, 999);
        }

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'city' => $validated['city'],
            'country' => $validated['country'] ?? 'USA',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'status' => 'active',
            'subscription_plan_id' => $validated['subscription_plan_id'],
        ]);

        // Create main branch
        $branch = \App\Models\Branch::create([
            'tenant_id' => $tenant->id,
            'name' => $tenant->name . ' HQ',
            'code' => 'HQ',
            'email' => $tenant->email,
            'phone' => $tenant->phone,
            'city' => $tenant->city,
            'is_main' => true,
            'status' => 'active',
        ]);

        // Create agency admin user
        $admin = User::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => $validated['admin_name'],
            'email' => $validated['admin_email'],
            'password' => bcrypt($validated['admin_password']),
            'role' => 'agency_admin',
            'status' => 'active',
        ]);

        // Create default pipeline for tenant
        $pipeline = \App\Models\Pipeline::create([
            'tenant_id' => $tenant->id,
            'name' => 'General Sales Pipeline',
            'is_default' => true,
        ]);
        \App\Models\PipelineStage::create(['tenant_id' => $tenant->id, 'pipeline_id' => $pipeline->id, 'name' => 'New Inquiry', 'slug' => 'new', 'order' => 1, 'probability' => 10, 'color' => '#64748B']);
        \App\Models\PipelineStage::create(['tenant_id' => $tenant->id, 'pipeline_id' => $pipeline->id, 'name' => 'Qualified', 'slug' => 'qualified', 'order' => 2, 'probability' => 25, 'color' => '#3B82F6']);
        \App\Models\PipelineStage::create(['tenant_id' => $tenant->id, 'pipeline_id' => $pipeline->id, 'name' => 'Site Visit', 'slug' => 'site-visit', 'order' => 3, 'probability' => 60, 'color' => '#8B5CF6']);
        \App\Models\PipelineStage::create(['tenant_id' => $tenant->id, 'pipeline_id' => $pipeline->id, 'name' => 'Booking', 'slug' => 'booking', 'order' => 4, 'probability' => 90, 'color' => '#10B981']);
        \App\Models\PipelineStage::create(['tenant_id' => $tenant->id, 'pipeline_id' => $pipeline->id, 'name' => 'Closed Won', 'slug' => 'won', 'order' => 5, 'probability' => 100, 'color' => '#059669', 'is_won' => true]);
        \App\Models\PipelineStage::create(['tenant_id' => $tenant->id, 'pipeline_id' => $pipeline->id, 'name' => 'Closed Lost', 'slug' => 'lost', 'order' => 6, 'probability' => 0, 'color' => '#EF4444', 'is_lost' => true]);

        AuditLog::record("Agency Tenant '{$tenant->name}' Onboarded", $tenant);

        return back()->with('success', "Tenant agency '{$tenant->name}' onboarded successfully!");
    }

    /**
     * Toggle tenant status (e.g. suspend or activate).
     */
    public function toggleStatus(Request $request, int $id)
    {
        $tenant = Tenant::findOrFail($id);
        $newStatus = $tenant->status === 'active' ? 'suspended' : 'active';
        $tenant->update(['status' => $newStatus]);

        AuditLog::record("Tenant '{$tenant->name}' status changed to {$newStatus}", $tenant);

        return back()->with('success', "Tenant agency status set to {$newStatus}.");
    }

    /**
     * Manage Subscription Plans.
     */
    public function plans(): Response
    {
        $plans = SubscriptionPlan::withCount('tenants')->get();

        return Inertia::render('SuperAdmin/Plans', [
            'plans' => $plans,
        ]);
    }

    /**
     * Store new subscription plan.
     */
    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'billing_interval' => 'required|in:monthly,yearly',
            'max_agents' => 'required|integer|min:1',
            'max_properties' => 'required|integer|min:1',
            'max_storage_mb' => 'required|integer|min:100',
            'features' => 'nullable|array',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        SubscriptionPlan::create($validated);

        return back()->with('success', 'Subscription plan created.');
    }
}
