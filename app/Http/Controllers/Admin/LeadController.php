<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\BuyerPreference;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    /**
     * Display a listing of leads.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Lead::with(['assignedAgent', 'customer', 'property', 'branch']);

        // Agent contextual scoping
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
                  ->orWhere('preferred_location', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Source Filter
        if ($request->filled('source') && $request->input('source') !== 'all') {
            $query->where('source', $request->input('source'));
        }

        // Priority Filter
        if ($request->filled('priority') && $request->input('priority') !== 'all') {
            $query->where('priority', $request->input('priority'));
        }

        // Assigned Agent Filter
        if ($request->filled('agent_id') && $request->input('agent_id') !== 'all') {
            $query->where('assigned_agent_id', $request->input('agent_id'));
        }

        // Sorting
        $sortKey = $request->input('sortKey', 'created_at');
        $sortDir = $request->input('sortDirection', 'desc');
        $allowedSorts = ['id', 'name', 'score', 'status', 'created_at', 'budget_max', 'priority'];
        if (in_array($sortKey, $allowedSorts)) {
            $query->orderBy($sortKey, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $perPage = (int) $request->input('perPage', 10);
        $leads = $query->paginate($perPage)->withQueryString();

        // Counters
        $baseCountQuery = Lead::query();
        if ($user->isAgent()) {
            $baseCountQuery->where('assigned_agent_id', $user->id);
        }

        $stats = [
            'total' => (clone $baseCountQuery)->count(),
            'new' => (clone $baseCountQuery)->where('status', 'new')->count(),
            'contacted' => (clone $baseCountQuery)->where('status', 'contacted')->count(),
            'qualified' => (clone $baseCountQuery)->where('status', 'qualified')->count(),
            'negotiation' => (clone $baseCountQuery)->where('status', 'negotiation')->count(),
            'converted' => (clone $baseCountQuery)->where('status', 'converted')->count(),
            'lost' => (clone $baseCountQuery)->where('status', 'lost')->count(),
        ];

        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->select('id', 'name')->get();

        return Inertia::render('Admin/Leads/Index', [
            'leads' => $leads,
            'filters' => $request->only(['search', 'status', 'source', 'priority', 'agent_id', 'sortKey', 'sortDirection', 'perPage']),
            'stats' => $stats,
            'agents' => $agents,
        ]);
    }

    /**
     * Show form to create lead.
     */
    public function create(): Response
    {
        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->select('id', 'name')->get();
        $branches = Branch::where('status', 'active')->select('id', 'name')->get();
        $properties = Property::whereIn('status', ['available', 'under_negotiation'])->select('id', 'title', 'property_code', 'price')->get();

        return Inertia::render('Admin/Leads/Create', [
            'agents' => $agents,
            'branches' => $branches,
            'properties' => $properties,
        ]);
    }

    /**
     * Store new lead in database.
     */
    public function store(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'lead_type' => 'required|string',
            'source' => 'required|string',
            'status' => 'required|string',
            'priority' => 'required|string',
            'score' => 'nullable|integer|min:0|max:100',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'preferred_location' => 'nullable|string|max:255',
            'property_type' => 'nullable|string',
            'requirements' => 'nullable|string',
            'assigned_agent_id' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            'property_id' => 'nullable|exists:properties,id',
            'tags' => 'nullable|array',
        ]);

        // Duplicate Lead Detection
        if ($tenantId) {
            $duplicate = Lead::checkDuplicate($tenantId, $validated['phone'], $validated['email'] ?? null);
            if ($duplicate) {
                // If force create wasn't passed, return with warning or note
                session()->flash('warning', "Notice: A lead with this contact already exists (#{$duplicate->id} - {$duplicate->name}). Lead was still recorded.");
            }
        }

        if (empty($validated['score'])) {
            $validated['score'] = 50;
            if ($validated['budget_max'] > 1000000) $validated['score'] += 20;
            if ($validated['priority'] === 'urgent') $validated['score'] += 20;
        }

        $lead = Lead::create($validated);

        AuditLog::record('Lead Created', $lead, null, $lead->toArray());

        return redirect()->route('admin.leads.show', $lead->id)
            ->with('success', "Lead '{$lead->name}' registered successfully.");
    }

    /**
     * Show lead details with timeline, site visits, and matches.
     */
    public function show(int $id): Response
    {
        $lead = Lead::with([
            'assignedAgent',
            'customer.preferences',
            'property',
            'branch',
            'siteVisits.property',
            'tasks.assignedUser',
            'deals.stage',
            'propertyMatches.property',
        ])->findOrFail($id);

        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->select('id', 'name')->get();
        $pipelines = Pipeline::with('stages')->get();

        return Inertia::render('Admin/Leads/Show', [
            'lead' => $lead,
            'agents' => $agents,
            'pipelines' => $pipelines,
        ]);
    }

    /**
     * Edit lead.
     */
    public function edit(int $id): Response
    {
        $lead = Lead::findOrFail($id);
        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->select('id', 'name')->get();
        $branches = Branch::where('status', 'active')->select('id', 'name')->get();
        $properties = Property::whereIn('status', ['available', 'under_negotiation'])->select('id', 'title', 'property_code', 'price')->get();

        return Inertia::render('Admin/Leads/Edit', [
            'lead' => $lead,
            'agents' => $agents,
            'branches' => $branches,
            'properties' => $properties,
        ]);
    }

    /**
     * Update lead details.
     */
    public function update(Request $request, int $id)
    {
        $lead = Lead::findOrFail($id);
        $old = $lead->toArray();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'lead_type' => 'required|string',
            'source' => 'required|string',
            'status' => 'required|string',
            'priority' => 'required|string',
            'score' => 'nullable|integer|min:0|max:100',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'preferred_location' => 'nullable|string|max:255',
            'property_type' => 'nullable|string',
            'requirements' => 'nullable|string',
            'assigned_agent_id' => 'nullable|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
            'property_id' => 'nullable|exists:properties,id',
            'tags' => 'nullable|array',
            'lost_reason' => 'nullable|string',
        ]);

        $lead->update($validated);

        AuditLog::record('Lead Updated', $lead, $old, $lead->toArray());

        return redirect()->route('admin.leads.show', $lead->id)
            ->with('success', 'Lead updated successfully.');
    }

    /**
     * Assign lead to an agent.
     */
    public function assign(Request $request, int $id)
    {
        $validated = $request->validate([
            'assigned_agent_id' => 'required|exists:users,id',
        ]);

        $lead = Lead::findOrFail($id);
        $lead->update(['assigned_agent_id' => $validated['assigned_agent_id']]);

        $agent = User::find($validated['assigned_agent_id']);
        AuditLog::record("Lead Assigned to {$agent->name}", $lead);

        return back()->with('success', "Lead assigned to {$agent->name}.");
    }

    /**
     * Convert lead into a Customer and optionally create an Opportunity/Deal.
     */
    public function convert(Request $request, int $id)
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'create_deal' => 'boolean',
            'deal_title' => 'nullable|string|max:255',
            'pipeline_id' => 'nullable|exists:pipelines,id',
            'stage_id' => 'nullable|exists:pipeline_stages,id',
            'expected_value' => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
        ]);

        // 1. Find or create Customer
        $customer = Customer::firstOrCreate(
            [
                'tenant_id' => $lead->tenant_id,
                'phone' => $lead->phone,
            ],
            [
                'name' => $lead->name,
                'email' => $lead->email,
                'customer_type' => $lead->lead_type === 'renter' ? 'tenant' : ($lead->lead_type === 'seller' ? 'seller' : 'buyer'),
                'assigned_agent_id' => $lead->assigned_agent_id,
                'source' => $lead->source,
                'city' => $lead->preferred_location,
                'notes' => "Converted from Lead #{$lead->id}. " . $lead->requirements,
            ]
        );

        // 2. Create Buyer Preference
        if ($lead->lead_type === 'buyer' || $lead->lead_type === 'renter') {
            BuyerPreference::updateOrCreate(
                [
                    'tenant_id' => $lead->tenant_id,
                    'customer_id' => $customer->id,
                ],
                [
                    'purpose' => $lead->lead_type === 'renter' ? 'rent' : 'sale',
                    'property_types' => $lead->property_type ? [$lead->property_type] : null,
                    'min_budget' => $lead->budget_min,
                    'max_budget' => $lead->budget_max,
                    'preferred_locations' => $lead->preferred_location ? [$lead->preferred_location] : null,
                ]
            );
        }

        // 3. Optionally create Deal
        $deal = null;
        if (!empty($validated['create_deal'])) {
            $pipeline = Pipeline::firstOrCreate(
                ['tenant_id' => $lead->tenant_id, 'is_default' => true],
                ['name' => 'Standard Sales Pipeline', 'deal_type' => 'sale']
            );
            $pipelineId = $validated['pipeline_id'] ?? $pipeline->id;

            $stage = PipelineStage::firstOrCreate(
                ['tenant_id' => $lead->tenant_id, 'pipeline_id' => $pipelineId, 'order' => 1],
                ['name' => 'New Opportunity', 'probability' => 20, 'is_won' => false, 'is_lost' => false]
            );
            $stageId = $validated['stage_id'] ?? $stage->id;

            $dealCount = Deal::count() + 1;
            $dealCode = 'DEAL-' . date('Y') . '-' . str_pad((string) $dealCount, 3, '0', STR_PAD_LEFT);

            $deal = Deal::create([
                'tenant_id' => $lead->tenant_id,
                'pipeline_id' => $pipelineId,
                'stage_id' => $stageId,
                'customer_id' => $customer->id,
                'lead_id' => $lead->id,
                'property_id' => $lead->property_id,
                'assigned_agent_id' => $lead->assigned_agent_id ?: $request->user()->id,
                'deal_code' => $dealCode,
                'title' => $validated['deal_title'] ?: "{$customer->name} - Deal",
                'deal_type' => $lead->lead_type === 'renter' ? 'rent' : 'sale',
                'expected_value' => $validated['expected_value'] ?: ($lead->budget_max ?: 500000),
                'expected_close_date' => $validated['expected_close_date'] ?: now()->addDays(30)->toDateString(),
            ]);
        }

        // 4. Update Lead status to converted
        $lead->update([
            'status' => 'converted',
            'customer_id' => $customer->id,
        ]);

        AuditLog::record("Lead Converted to Customer #{$customer->id}" . ($deal ? " & Deal #{$deal->deal_code}" : ''), $lead);

        return redirect()->route('admin.customers.show', $customer->id)
            ->with('success', "Lead successfully converted to Customer '{$customer->name}'!" . ($deal ? " Created Deal #{$deal->deal_code}." : ''));
    }

    /**
     * Delete/archive lead.
     */
    public function destroy(int $id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();

        AuditLog::record('Lead Archived', $lead);

        return redirect()->route('admin.leads.index')
            ->with('success', 'Lead archived successfully.');
    }
}
