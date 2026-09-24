<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BuyerPreference;
use App\Models\Customer;
use App\Models\Property;
use App\Models\PropertyMatch;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Customer::with(['assignedAgent', 'preferences']);

        if ($user->isAgent()) {
            $query->where('assigned_agent_id', $user->id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // Customer Type Filter
        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('customer_type', $request->input('type'));
        }

        // Status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Sorting
        $sortKey = $request->input('sortKey', 'created_at');
        $sortDir = $request->input('sortDirection', 'desc');
        $allowedSorts = ['id', 'name', 'customer_type', 'created_at', 'city'];
        if (in_array($sortKey, $allowedSorts)) {
            $query->orderBy($sortKey, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $perPage = (int) $request->input('perPage', 10);
        $customers = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total' => Customer::count(),
            'buyers' => Customer::where('customer_type', 'buyer')->count(),
            'sellers' => Customer::where('customer_type', 'seller')->count(),
            'tenants' => Customer::where('customer_type', 'tenant')->count(),
            'investors' => Customer::where('customer_type', 'investor')->count(),
        ];

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search', 'type', 'status', 'sortKey', 'sortDirection', 'perPage']),
            'stats' => $stats,
        ]);
    }

    /**
     * Show form to create customer.
     */
    public function create(): Response
    {
        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->select('id', 'name')->get();

        return Inertia::render('Admin/Customers/Create', [
            'agents' => $agents,
        ]);
    }

    /**
     * Store new customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'secondary_phone' => 'nullable|string|max:50',
            'customer_type' => 'required|string',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'assigned_agent_id' => 'nullable|exists:users,id',
            'source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'required|string',
            // Buyer Preferences
            'purpose' => 'nullable|string',
            'property_types' => 'nullable|array',
            'min_budget' => 'nullable|numeric|min:0',
            'max_budget' => 'nullable|numeric|min:0',
            'preferred_locations' => 'nullable|array',
            'min_bedrooms' => 'nullable|integer|min:0',
            'min_bathrooms' => 'nullable|integer|min:0',
            'min_area' => 'nullable|numeric|min:0',
            'possession_timeline' => 'nullable|string',
            'furnishing' => 'nullable|string',
        ]);

        $prefData = [
            'purpose' => $validated['purpose'] ?? 'sale',
            'property_types' => $validated['property_types'] ?? null,
            'min_budget' => $validated['min_budget'] ?? null,
            'max_budget' => $validated['max_budget'] ?? null,
            'preferred_locations' => $validated['preferred_locations'] ?? null,
            'min_bedrooms' => $validated['min_bedrooms'] ?? null,
            'min_bathrooms' => $validated['min_bathrooms'] ?? null,
            'min_area' => $validated['min_area'] ?? null,
            'possession_timeline' => $validated['possession_timeline'] ?? null,
            'furnishing' => $validated['furnishing'] ?? null,
        ];

        unset(
            $validated['purpose'], $validated['property_types'], $validated['min_budget'],
            $validated['max_budget'], $validated['preferred_locations'], $validated['min_bedrooms'],
            $validated['min_bathrooms'], $validated['min_area'], $validated['possession_timeline'],
            $validated['furnishing']
        );

        $customer = Customer::create($validated);

        if (in_array($customer->customer_type, ['buyer', 'tenant', 'investor'])) {
            BuyerPreference::create([
                'tenant_id' => $customer->tenant_id,
                'customer_id' => $customer->id,
                ...$prefData,
            ]);
        }

        AuditLog::record('Customer Created', $customer, null, $customer->toArray());

        return redirect()->route('admin.customers.show', $customer->id)
            ->with('success', "Customer '{$customer->name}' created successfully.");
    }

    /**
     * Show 360 customer profile.
     */
    public function show(int $id): Response
    {
        $customer = Customer::with([
            'preferences',
            'assignedAgent',
            'ownedProperties',
            'leads',
            'deals.stage',
            'deals.property',
            'siteVisits.property',
            'siteVisits.assignedAgent',
            'bookings.property',
            'payments',
            'propertyMatches.property.images',
        ])->findOrFail($id);

        return Inertia::render('Admin/Customers/Show', [
            'customer' => $customer,
        ]);
    }

    /**
     * Edit customer.
     */
    public function edit(int $id): Response
    {
        $customer = Customer::with('preferences')->findOrFail($id);
        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->select('id', 'name')->get();

        return Inertia::render('Admin/Customers/Edit', [
            'customer' => $customer,
            'agents' => $agents,
        ]);
    }

    /**
     * Update customer.
     */
    public function update(Request $request, int $id)
    {
        $customer = Customer::findOrFail($id);
        $old = $customer->toArray();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'secondary_phone' => 'nullable|string|max:50',
            'customer_type' => 'required|string',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'assigned_agent_id' => 'nullable|exists:users,id',
            'source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'required|string',
            // Buyer Preferences
            'purpose' => 'nullable|string',
            'property_types' => 'nullable|array',
            'min_budget' => 'nullable|numeric|min:0',
            'max_budget' => 'nullable|numeric|min:0',
            'preferred_locations' => 'nullable|array',
            'min_bedrooms' => 'nullable|integer|min:0',
            'min_bathrooms' => 'nullable|integer|min:0',
            'min_area' => 'nullable|numeric|min:0',
            'possession_timeline' => 'nullable|string',
            'furnishing' => 'nullable|string',
        ]);

        $prefData = [
            'purpose' => $validated['purpose'] ?? 'sale',
            'property_types' => $validated['property_types'] ?? null,
            'min_budget' => $validated['min_budget'] ?? null,
            'max_budget' => $validated['max_budget'] ?? null,
            'preferred_locations' => $validated['preferred_locations'] ?? null,
            'min_bedrooms' => $validated['min_bedrooms'] ?? null,
            'min_bathrooms' => $validated['min_bathrooms'] ?? null,
            'min_area' => $validated['min_area'] ?? null,
            'possession_timeline' => $validated['possession_timeline'] ?? null,
            'furnishing' => $validated['furnishing'] ?? null,
        ];

        unset(
            $validated['purpose'], $validated['property_types'], $validated['min_budget'],
            $validated['max_budget'], $validated['preferred_locations'], $validated['min_bedrooms'],
            $validated['min_bathrooms'], $validated['min_area'], $validated['possession_timeline'],
            $validated['furnishing']
        );

        $customer->update($validated);

        if (in_array($customer->customer_type, ['buyer', 'tenant', 'investor'])) {
            BuyerPreference::updateOrCreate(
                [
                    'tenant_id' => $customer->tenant_id,
                    'customer_id' => $customer->id,
                ],
                $prefData
            );
        }

        AuditLog::record('Customer Updated', $customer, $old, $customer->toArray());

        return redirect()->route('admin.customers.show', $customer->id)
            ->with('success', 'Customer profile updated successfully.');
    }

    /**
     * Delete customer.
     */
    public function destroy(int $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        AuditLog::record('Customer Archived', $customer);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer archived.');
    }
}
