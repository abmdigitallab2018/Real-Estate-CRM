<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Property;
use App\Models\SiteVisit;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPropertyController extends Controller
{
    /**
     * Browse published public properties
     */
    public function index(Request $request): Response
    {
        $query = Property::withoutGlobalScope('tenant')
            ->with(['images', 'amenities', 'listingAgent', 'tenant'])
            ->where('is_published', true)
            ->whereIn('status', ['available', 'under_negotiation']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('locality', 'like', "%{$search}%")
                  ->orWhere('property_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('purpose') && $request->input('purpose') !== 'all') {
            $query->where('listing_purpose', $request->input('purpose'));
        }

        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('property_type', $request->input('type'));
        }

        if ($request->filled('city') && $request->input('city') !== 'all') {
            $query->where('city', $request->input('city'));
        }

        if ($request->filled('bedrooms') && $request->input('bedrooms') !== 'all') {
            $query->where('bedrooms', '>=', (int) $request->input('bedrooms'));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', (float) $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', (float) $request->input('max_price'));
        }

        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'featured':
                $query->orderByDesc('is_featured')->orderByDesc('id');
                break;
            case 'latest':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        $properties = $query->paginate(12)->withQueryString();

        $cities = Property::withoutGlobalScope('tenant')
            ->where('is_published', true)
            ->distinct()
            ->pluck('city');

        return Inertia::render('Public/PropertiesIndex', [
            'properties' => $properties,
            'filters' => $request->only(['search', 'purpose', 'type', 'city', 'bedrooms', 'min_price', 'max_price', 'sort']),
            'availableCities' => $cities,
        ]);
    }

    /**
     * Show single public property details
     */
    public function show(string $slug): Response
    {
        $property = Property::withoutGlobalScope('tenant')
            ->with(['images', 'amenities', 'listingAgent', 'tenant', 'branch'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Increment views
        $property->increment('views_count');

        $similarProperties = Property::withoutGlobalScope('tenant')
            ->with('images')
            ->where('tenant_id', $property->tenant_id)
            ->where('id', '!=', $property->id)
            ->where('property_type', $property->property_type)
            ->where('status', 'available')
            ->take(3)
            ->get();

        return Inertia::render('Public/PropertyDetail', [
            'property' => $property,
            'similarProperties' => $similarProperties,
        ]);
    }

    /**
     * Submit an inquiry from public property page -> creates Lead in CRM
     */
    public function submitInquiry(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:50',
            'message' => 'nullable|string|max:1000',
        ]);

        $property = Property::withoutGlobalScope('tenant')->findOrFail($validated['property_id']);

        // Check or create lead
        Lead::withoutGlobalScope('tenant')->create([
            'tenant_id' => $property->tenant_id,
            'branch_id' => $property->branch_id,
            'assigned_agent_id' => $property->listing_agent_id,
            'property_id' => $property->id,
            'lead_type' => in_array($property->listing_purpose, ['rent', 'lease']) ? 'renter' : 'buyer',
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'source' => 'website',
            'status' => 'new',
            'priority' => 'high',
            'score' => 60,
            'budget_min' => $property->price ? $property->price * 0.9 : null,
            'budget_max' => $property->price ? $property->price * 1.1 : null,
            'preferred_location' => $property->locality ?: $property->city,
            'property_type' => $property->property_type,
            'requirements' => "Inquiry on {$property->title} ({$property->property_code}): " . ($validated['message'] ?? 'Customer requested information.'),
            'tags' => ['Website Inquiry', $property->property_code],
        ]);

        return back()->with('success', 'Thank you! Your inquiry has been received. One of our property consultants will contact you shortly.');
    }

    /**
     * Schedule a site visit request from public page
     */
    public function scheduleVisit(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:50',
            'visit_date' => 'required|date|after_or_equal:today',
            'visit_time' => 'required',
            'notes' => 'nullable|string|max:500',
        ]);

        $property = Property::withoutGlobalScope('tenant')->findOrFail($validated['property_id']);

        // 1. Create or find lead
        $lead = Lead::withoutGlobalScope('tenant')->create([
            'tenant_id' => $property->tenant_id,
            'branch_id' => $property->branch_id,
            'assigned_agent_id' => $property->listing_agent_id,
            'property_id' => $property->id,
            'lead_type' => in_array($property->listing_purpose, ['rent', 'lease']) ? 'renter' : 'buyer',
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'source' => 'website',
            'status' => 'site_visit_scheduled',
            'priority' => 'urgent',
            'score' => 75,
            'budget_min' => $property->price,
            'budget_max' => $property->price,
            'preferred_location' => $property->city,
            'property_type' => $property->property_type,
            'requirements' => "Requested showing for {$property->title} on {$validated['visit_date']} at {$validated['visit_time']}. " . ($validated['notes'] ?? ''),
            'tags' => ['Site Visit Request', $property->property_code],
        ]);

        // 2. Also create customer if needed
        $customer = \App\Models\Customer::withoutGlobalScope('tenant')->firstOrCreate(
            ['tenant_id' => $property->tenant_id, 'phone' => $validated['phone']],
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'assigned_agent_id' => $property->listing_agent_id,
                'customer_type' => 'buyer',
                'source' => 'Website Site Visit Form',
                'city' => $property->city,
            ]
        );

        $lead->update(['customer_id' => $customer->id]);

        // 3. Create site visit record
        $visitCode = 'VST-' . date('Y') . '-' . str_pad((string) (SiteVisit::withoutGlobalScope('tenant')->count() + 1), 4, '0', STR_PAD_LEFT);

        SiteVisit::withoutGlobalScope('tenant')->create([
            'tenant_id' => $property->tenant_id,
            'property_id' => $property->id,
            'customer_id' => $customer->id,
            'assigned_agent_id' => $property->listing_agent_id ?: 1,
            'lead_id' => $lead->id,
            'visit_code' => $visitCode,
            'scheduled_date' => $validated['visit_date'],
            'scheduled_time' => $validated['visit_time'],
            'status' => 'scheduled',
            'customer_feedback' => null,
            'agent_notes' => 'Booked through public website: ' . ($validated['notes'] ?? 'None'),
            'next_action' => 'Call buyer to confirm directions & timing',
        ]);

        return back()->with('success', 'Your site visit has been scheduled! Our agent will call you to confirm your appointment.');
    }

    /**
     * Pricing page for SaaS plans
     */
    public function pricing(): Response
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('price')->get();

        return Inertia::render('Public/Pricing', [
            'plans' => $plans,
        ]);
    }
}
