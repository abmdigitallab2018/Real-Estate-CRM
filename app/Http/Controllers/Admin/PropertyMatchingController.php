<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BuyerPreference;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Property;
use App\Models\PropertyMatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PropertyMatchingController extends Controller
{
    /**
     * Show matching engine view for a customer or lead.
     */
    public function index(Request $request): Response
    {
        $customerId = $request->input('customer_id');
        $leadId = $request->input('lead_id');

        $customer = null;
        $lead = null;
        $preferences = null;

        if ($customerId) {
            $customer = Customer::with('preferences')->findOrFail($customerId);
            $preferences = $customer->preferences;
        } elseif ($leadId) {
            $lead = Lead::findOrFail($leadId);
            if ($lead->customer_id) {
                $customer = Customer::with('preferences')->find($lead->customer_id);
                $preferences = $customer?->preferences;
            }
        }

        // Available properties only (strictly exclude reserved, sold, inactive)
        $query = Property::with(['images', 'amenities', 'listingAgent'])
            ->where('status', 'available');

        // Apply filters or compute match scores
        $matches = [];
        $allAvailable = $query->get();

        foreach ($allAvailable as $prop) {
            $score = 50; // Base score
            $matchReasons = [];

            // Budget Match (30 pts)
            $minBudget = $preferences?->min_budget ?? ($lead?->budget_min);
            $maxBudget = $preferences?->max_budget ?? ($lead?->budget_max);
            $propPrice = $prop->effective_price;

            if ($minBudget && $maxBudget) {
                if ($propPrice >= $minBudget && $propPrice <= $maxBudget) {
                    $score += 30;
                    $matchReasons[] = 'Within Budget';
                } elseif ($propPrice <= $maxBudget * 1.15) {
                    $score += 15;
                    $matchReasons[] = 'Slightly above budget (within 15%)';
                }
            } elseif ($maxBudget && $propPrice <= $maxBudget) {
                $score += 30;
                $matchReasons[] = 'Within Budget';
            }

            // Location Match (20 pts)
            $prefLocations = $preferences?->preferred_locations ?? ($lead?->preferred_location ? [$lead->preferred_location] : []);
            if (!empty($prefLocations)) {
                foreach ($prefLocations as $loc) {
                    if (
                        stripos($prop->city, $loc) !== false ||
                        stripos($prop->locality, $loc) !== false ||
                        stripos($prop->address, $loc) !== false
                    ) {
                        $score += 20;
                        $matchReasons[] = "Location matched ({$loc})";
                        break;
                    }
                }
            }

            // Bedrooms Match (15 pts)
            $minBeds = $preferences?->min_bedrooms;
            if ($minBeds && $prop->bedrooms >= $minBeds) {
                $score += 15;
                $matchReasons[] = "Bedrooms satisfied ({$prop->bedrooms} BHK)";
            }

            // Property Type Match (15 pts)
            $prefTypes = $preferences?->property_types ?? ($lead?->property_type ? [$lead->property_type] : []);
            if (!empty($prefTypes) && in_array($prop->property_type, $prefTypes)) {
                $score += 15;
                $matchReasons[] = "Property type: {$prop->property_type}";
            }

            $score = min(100, max(10, $score));

            // Check if already shortlisted or shared
            $existingMatch = null;
            if ($customer) {
                $existingMatch = PropertyMatch::where('customer_id', $customer->id)
                    ->where('property_id', $prop->id)
                    ->first();
            }

            $matches[] = [
                'property' => $prop,
                'match_score' => $score,
                'match_reasons' => $matchReasons,
                'match_record' => $existingMatch,
            ];
        }

        // Sort descending by match score
        usort($matches, fn ($a, $b) => $b['match_score'] <=> $a['match_score']);

        $allCustomers = Customer::where('status', 'active')->select('id', 'name', 'phone', 'customer_type')->get();

        return Inertia::render('Admin/Matching/Index', [
            'matches' => $matches,
            'selectedCustomer' => $customer,
            'selectedLead' => $lead,
            'customers' => $allCustomers,
        ]);
    }

    /**
     * Shortlist a property for a customer.
     */
    public function shortlist(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'property_id' => 'required|exists:properties,id',
            'lead_id' => 'nullable|exists:leads,id',
            'match_score' => 'nullable|integer',
        ]);

        $match = PropertyMatch::updateOrCreate(
            [
                'tenant_id' => $request->user()->tenant_id,
                'customer_id' => $validated['customer_id'],
                'property_id' => $validated['property_id'],
            ],
            [
                'lead_id' => $validated['lead_id'] ?? null,
                'match_score' => $validated['match_score'] ?? 85,
                'status' => 'shortlisted',
            ]
        );

        AuditLog::record("Property #{$match->property_id} Shortlisted for Customer #{$match->customer_id}", $match);

        return back()->with('success', 'Property shortlisted successfully.');
    }

    /**
     * Log that property was shared with customer.
     */
    public function share(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'property_id' => 'required|exists:properties,id',
            'lead_id' => 'nullable|exists:leads,id',
        ]);

        $match = PropertyMatch::updateOrCreate(
            [
                'tenant_id' => $request->user()->tenant_id,
                'customer_id' => $validated['customer_id'],
                'property_id' => $validated['property_id'],
            ],
            [
                'lead_id' => $validated['lead_id'] ?? null,
                'status' => 'shared',
                'shared_at' => now(),
            ]
        );

        AuditLog::record("Property #{$match->property_id} Shared with Customer #{$match->customer_id}", $match);

        return back()->with('success', 'Property marked as shared with customer.');
    }

    /**
     * Record customer feedback on a matched property.
     */
    public function recordFeedback(Request $request)
    {
        $validated = $request->validate([
            'match_id' => 'required|exists:property_matches,id',
            'feedback' => 'required|string|max:1000',
            'status' => 'required|in:shortlisted,shared,rejected,visited',
        ]);

        $match = PropertyMatch::findOrFail($validated['match_id']);
        $match->update([
            'customer_feedback' => $validated['feedback'],
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Customer feedback recorded.');
    }
}
