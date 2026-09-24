<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Property;
use App\Models\PropertyAmenity;
use App\Models\PropertyImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties.
     */
    public function index(Request $request): Response
    {
        $query = Property::with(['images', 'listingAgent', 'owner', 'branch']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('property_code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('locality', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('purpose') && $request->input('purpose') !== 'all') {
            $query->where('listing_purpose', $request->input('purpose'));
        }

        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('property_type', $request->input('type'));
        }

        if ($request->filled('agent_id') && $request->input('agent_id') !== 'all') {
            $query->where('listing_agent_id', $request->input('agent_id'));
        }

        // Sorting
        $sortKey = $request->input('sortKey', 'created_at');
        $sortDir = $request->input('sortDirection', 'desc');
        $allowedSorts = ['id', 'title', 'price', 'created_at', 'status', 'property_code', 'city'];
        if (in_array($sortKey, $allowedSorts)) {
            $query->orderBy($sortKey, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $perPage = (int) $request->input('perPage', 10);
        $properties = $query->paginate($perPage)->withQueryString();

        // Summary counters
        $stats = [
            'total' => Property::count(),
            'available' => Property::where('status', 'available')->count(),
            'under_negotiation' => Property::where('status', 'under_negotiation')->count(),
            'reserved' => Property::where('status', 'reserved')->count(),
            'sold' => Property::where('status', 'sold')->count(),
            'rented' => Property::whereIn('status', ['rented', 'leased'])->count(),
        ];

        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->select('id', 'name')->get();

        return Inertia::render('Admin/Properties/Index', [
            'properties' => $properties,
            'filters' => $request->only(['search', 'status', 'purpose', 'type', 'agent_id', 'sortKey', 'sortDirection', 'perPage']),
            'stats' => $stats,
            'agents' => $agents,
        ]);
    }

    /**
     * Show form to create new property.
     */
    public function create(): Response
    {
        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->select('id', 'name')->get();
        $branches = Branch::where('status', 'active')->select('id', 'name')->get();
        $owners = Customer::where('status', 'active')->select('id', 'name', 'phone', 'customer_type')->get();

        $defaultAmenities = [
            'Swimming Pool', 'Gym & Fitness Center', 'Clubhouse', '24/7 Security & CCTV',
            'Power Backup', 'Elevator', 'Visitor Parking', 'Children Play Area',
            'Landscaped Garden', 'Intercom', 'Fire Safety', 'Gated Community'
        ];

        return Inertia::render('Admin/Properties/Create', [
            'agents' => $agents,
            'branches' => $branches,
            'owners' => $owners,
            'defaultAmenities' => $defaultAmenities,
        ]);
    }

    /**
     * Store new property in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'property_code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'property_type' => 'required|string',
            'listing_purpose' => 'required|string',
            'status' => 'required|string',
            'price' => 'required|numeric|min:0',
            'rent_amount' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'maintenance_charges' => 'nullable|numeric|min:0',
            'is_negotiable' => 'boolean',
            'address' => 'required|string|max:500',
            'locality' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'balconies' => 'nullable|integer|min:0',
            'carpet_area' => 'nullable|numeric|min:0',
            'built_up_area' => 'nullable|numeric|min:0',
            'area_unit' => 'nullable|string|max:20',
            'furnishing' => 'nullable|string',
            'floor' => 'nullable|integer',
            'total_floors' => 'nullable|integer',
            'parking_spaces' => 'nullable|integer|min:0',
            'construction_status' => 'nullable|string',
            'year_built' => 'nullable|integer',
            'available_from' => 'nullable|date',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'listing_agent_id' => 'nullable|exists:users,id',
            'owner_id' => 'nullable|exists:customers,id',
            'branch_id' => 'nullable|exists:branches,id',
            'featured_image' => 'nullable|string',
            'amenities' => 'nullable|array',
            'image_urls' => 'nullable|array',
        ]);

        // Auto-generate code if empty
        if (empty($validated['property_code'])) {
            $count = Property::count() + 1;
            $validated['property_code'] = 'PROP-' . (1000 + $count);
        }

        $validated['slug'] = Str::slug($validated['title']) . '-' . strtolower($validated['property_code']);

        $amenities = $validated['amenities'] ?? [];
        $imageUrls = $validated['image_urls'] ?? [];
        unset($validated['amenities'], $validated['image_urls']);

        $property = Property::create($validated);

        // Attach amenities
        foreach ($amenities as $name) {
            PropertyAmenity::create([
                'tenant_id' => $property->tenant_id,
                'property_id' => $property->id,
                'name' => $name,
            ]);
        }

        // Attach images
        if (!empty($imageUrls)) {
            foreach ($imageUrls as $idx => $url) {
                PropertyImage::create([
                    'tenant_id' => $property->tenant_id,
                    'property_id' => $property->id,
                    'image_path' => $url,
                    'is_primary' => $idx === 0,
                    'sort_order' => $idx + 1,
                ]);
            }
        } elseif ($property->featured_image) {
            PropertyImage::create([
                'tenant_id' => $property->tenant_id,
                'property_id' => $property->id,
                'image_path' => $property->featured_image,
                'is_primary' => true,
                'sort_order' => 1,
            ]);
        }

        AuditLog::record('Property Created', $property, null, $property->toArray());

        return redirect()->route('admin.properties.show', $property->id)
            ->with('success', "Property '{$property->title}' ({$property->property_code}) created successfully.");
    }

    /**
     * Display a specific property with all relations and history.
     */
    public function show(int $id): Response
    {
        $property = Property::with([
            'images',
            'amenities',
            'listingAgent',
            'owner',
            'branch',
            'siteVisits.customer',
            'siteVisits.assignedAgent',
            'deals.customer',
            'deals.stage',
            'bookings.customer',
            'documents',
        ])->findOrFail($id);

        $inquiries = \App\Models\Lead::where('property_id', $property->id)->latest()->get();
        $auditLogs = AuditLog::where('auditable_type', Property::class)->where('auditable_id', $property->id)->latest()->take(10)->get();

        return Inertia::render('Admin/Properties/Show', [
            'property' => $property,
            'inquiries' => $inquiries,
            'auditLogs' => $auditLogs,
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(int $id): Response
    {
        $property = Property::with(['images', 'amenities'])->findOrFail($id);
        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->select('id', 'name')->get();
        $branches = Branch::where('status', 'active')->select('id', 'name')->get();
        $owners = Customer::where('status', 'active')->select('id', 'name', 'phone', 'customer_type')->get();

        $defaultAmenities = [
            'Swimming Pool', 'Gym & Fitness Center', 'Clubhouse', '24/7 Security & CCTV',
            'Power Backup', 'Elevator', 'Visitor Parking', 'Children Play Area',
            'Landscaped Garden', 'Intercom', 'Fire Safety', 'Gated Community'
        ];

        return Inertia::render('Admin/Properties/Edit', [
            'property' => $property,
            'agents' => $agents,
            'branches' => $branches,
            'owners' => $owners,
            'defaultAmenities' => $defaultAmenities,
        ]);
    }

    /**
     * Update existing property.
     */
    public function update(Request $request, int $id)
    {
        $property = Property::findOrFail($id);
        $oldValues = $property->toArray();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'property_code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'property_type' => 'required|string',
            'listing_purpose' => 'required|string',
            'status' => 'required|string',
            'price' => 'required|numeric|min:0',
            'rent_amount' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'maintenance_charges' => 'nullable|numeric|min:0',
            'is_negotiable' => 'boolean',
            'address' => 'required|string|max:500',
            'locality' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'balconies' => 'nullable|integer|min:0',
            'carpet_area' => 'nullable|numeric|min:0',
            'built_up_area' => 'nullable|numeric|min:0',
            'area_unit' => 'nullable|string|max:20',
            'furnishing' => 'nullable|string',
            'floor' => 'nullable|integer',
            'total_floors' => 'nullable|integer',
            'parking_spaces' => 'nullable|integer|min:0',
            'construction_status' => 'nullable|string',
            'year_built' => 'nullable|integer',
            'available_from' => 'nullable|date',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'listing_agent_id' => 'nullable|exists:users,id',
            'owner_id' => 'nullable|exists:customers,id',
            'branch_id' => 'nullable|exists:branches,id',
            'featured_image' => 'nullable|string',
            'amenities' => 'nullable|array',
            'image_urls' => 'nullable|array',
        ]);

        $amenities = $validated['amenities'] ?? [];
        $imageUrls = $validated['image_urls'] ?? [];
        unset($validated['amenities'], $validated['image_urls']);

        $property->update($validated);

        // Sync amenities
        PropertyAmenity::where('property_id', $property->id)->delete();
        foreach ($amenities as $name) {
            PropertyAmenity::create([
                'tenant_id' => $property->tenant_id,
                'property_id' => $property->id,
                'name' => $name,
            ]);
        }

        // Sync images if new ones provided
        if (!empty($imageUrls)) {
            PropertyImage::where('property_id', $property->id)->delete();
            foreach ($imageUrls as $idx => $url) {
                PropertyImage::create([
                    'tenant_id' => $property->tenant_id,
                    'property_id' => $property->id,
                    'image_path' => $url,
                    'is_primary' => $idx === 0,
                    'sort_order' => $idx + 1,
                ]);
            }
        }

        AuditLog::record('Property Updated', $property, $oldValues, $property->toArray());

        return redirect()->route('admin.properties.show', $property->id)
            ->with('success', 'Property updated successfully.');
    }

    /**
     * Update status quickly.
     */
    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,available,under_negotiation,reserved,sold,rented,leased,inactive',
        ]);

        $property = Property::findOrFail($id);
        $oldStatus = $property->status;
        $property->update(['status' => $validated['status']]);

        AuditLog::record("Property Status changed from {$oldStatus} to {$validated['status']}", $property);

        return back()->with('success', "Property status changed to {$validated['status']}.");
    }

    /**
     * Remove property.
     */
    public function destroy(int $id)
    {
        $property = Property::findOrFail($id);
        $property->delete();

        AuditLog::record('Property Archived', $property);

        return redirect()->route('admin.properties.index')
            ->with('success', 'Property listing archived.');
    }
}
