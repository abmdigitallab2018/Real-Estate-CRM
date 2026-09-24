<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DealController extends Controller
{
    /**
     * Display Kanban board or table view of deals.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Selected pipeline
        $pipelineId = $request->input('pipeline_id');
        $pipeline = $pipelineId
            ? Pipeline::with('stages')->findOrFail($pipelineId)
            : Pipeline::with('stages')->where('is_default', true)->first() ?? Pipeline::with('stages')->first();

        $allPipelines = Pipeline::select('id', 'name', 'is_default')->get();

        $dealQuery = Deal::with(['customer', 'property', 'assignedAgent', 'stage'])
            ->where('pipeline_id', $pipeline->id);

        if ($user->isAgent()) {
            $dealQuery->where('assigned_agent_id', $user->id);
        }

        if ($request->filled('agent_id') && $request->input('agent_id') !== 'all') {
            $dealQuery->where('assigned_agent_id', $request->input('agent_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $dealQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('deal_code', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('property', fn ($pq) => $pq->where('title', 'like', "%{$search}%"));
            });
        }

        $allDeals = $dealQuery->get();

        // Group deals by stage for Kanban
        $stagesWithDeals = $pipeline->stages->map(function ($stage) use ($allDeals) {
            $stageDeals = $allDeals->where('stage_id', $stage->id)->values();
            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'slug' => $stage->slug,
                'color' => $stage->color,
                'probability' => $stage->probability,
                'is_won' => $stage->is_won,
                'is_lost' => $stage->is_lost,
                'deals_count' => $stageDeals->count(),
                'total_value' => (float) $stageDeals->sum('expected_value'),
                'deals' => $stageDeals,
            ];
        });

        // Summary stats
        $totalPipelineValue = (float) $allDeals->sum('expected_value');
        $weightedPipelineValue = (float) $allDeals->sum(fn ($d) => $d->expected_value * (($d->stage?->probability ?? 50) / 100));

        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->select('id', 'name')->get();
        $properties = Property::whereIn('status', ['available', 'under_negotiation', 'reserved'])->select('id', 'title', 'property_code', 'price')->get();
        $customers = Customer::where('status', 'active')->select('id', 'name', 'phone')->get();

        return Inertia::render('Admin/Deals/Index', [
            'currentPipeline' => $pipeline,
            'pipelines' => $allPipelines,
            'stagesWithDeals' => $stagesWithDeals,
            'totalPipelineValue' => $totalPipelineValue,
            'weightedPipelineValue' => $weightedPipelineValue,
            'agents' => $agents,
            'properties' => $properties,
            'customers' => $customers,
            'filters' => $request->only(['pipeline_id', 'agent_id', 'search']),
        ]);
    }

    /**
     * Store new deal in pipeline.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pipeline_id' => 'required|exists:pipelines,id',
            'stage_id' => 'required|exists:pipeline_stages,id',
            'customer_id' => 'required|exists:customers,id',
            'property_id' => 'nullable|exists:properties,id',
            'assigned_agent_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'deal_type' => 'required|in:sale,rent,lease',
            'expected_value' => 'required|numeric|min:0',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $dealCount = Deal::count() + 1;
        $validated['deal_code'] = 'DEAL-' . date('Y') . '-' . str_pad((string) $dealCount, 3, '0', STR_PAD_LEFT);

        if (empty($validated['probability'])) {
            $stage = PipelineStage::find($validated['stage_id']);
            $validated['probability'] = $stage?->probability ?? 50;
        }

        $deal = Deal::create($validated);

        // If property attached and status is available, set to under_negotiation
        if ($deal->property_id) {
            $prop = Property::find($deal->property_id);
            if ($prop && $prop->status === 'available') {
                $prop->update(['status' => 'under_negotiation']);
            }
        }

        AuditLog::record('Deal Created', $deal, null, $deal->toArray());

        return back()->with('success', "Deal '{$deal->title}' ({$deal->deal_code}) added to pipeline.");
    }

    /**
     * Show deal details with timeline and negotiations.
     */
    public function show(int $id): Response
    {
        $deal = Deal::with([
            'customer',
            'property.images',
            'assignedAgent',
            'stage',
            'pipeline.stages',
            'siteVisits',
            'bookings.payments',
            'payments',
            'commissions',
        ])->findOrFail($id);

        return Inertia::render('Admin/Deals/Show', [
            'deal' => $deal,
        ]);
    }

    /**
     * Update deal stage via drag-and-drop or modal.
     */
    public function updateStage(Request $request, int $id)
    {
        $validated = $request->validate([
            'stage_id' => 'required|exists:pipeline_stages,id',
            'lost_reason' => 'nullable|string|max:500',
            'actual_value' => 'nullable|numeric|min:0',
        ]);

        $deal = Deal::findOrFail($id);
        $newStage = PipelineStage::findOrFail($validated['stage_id']);
        $oldStageName = $deal->stage?->name;

        $updateData = [
            'stage_id' => $newStage->id,
            'probability' => $newStage->probability,
        ];

        if ($newStage->is_won) {
            $updateData['closed_at'] = now();
            $updateData['actual_value'] = $validated['actual_value'] ?? $deal->expected_value;
            // If property attached, mark property as sold/rented
            if ($deal->property_id) {
                $prop = Property::find($deal->property_id);
                if ($prop) {
                    $prop->update(['status' => $deal->deal_type === 'sale' ? 'sold' : 'rented']);
                }
            }
        } elseif ($newStage->is_lost) {
            $updateData['closed_at'] = now();
            $updateData['lost_reason'] = $validated['lost_reason'] ?? 'Marked as lost';
            // If property was under negotiation, release back to available
            if ($deal->property_id) {
                $prop = Property::find($deal->property_id);
                if ($prop && $prop->status === 'under_negotiation') {
                    $prop->update(['status' => 'available']);
                }
            }
        } else {
            $updateData['closed_at'] = null;
        }

        $deal->update($updateData);

        AuditLog::record("Deal Stage Changed from {$oldStageName} to {$newStage->name}", $deal);

        return back()->with('success', "Deal moved to '{$newStage->name}'.");
    }

    /**
     * Update deal details.
     */
    public function update(Request $request, int $id)
    {
        $deal = Deal::findOrFail($id);
        $old = $deal->toArray();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'pipeline_id' => 'required|exists:pipelines,id',
            'stage_id' => 'required|exists:pipeline_stages,id',
            'customer_id' => 'required|exists:customers,id',
            'property_id' => 'nullable|exists:properties,id',
            'assigned_agent_id' => 'required|exists:users,id',
            'deal_type' => 'required|in:sale,rent,lease',
            'expected_value' => 'required|numeric|min:0',
            'actual_value' => 'nullable|numeric|min:0',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'lost_reason' => 'nullable|string',
        ]);

        $deal->update($validated);

        AuditLog::record('Deal Updated', $deal, $old, $deal->toArray());

        return back()->with('success', 'Deal updated successfully.');
    }

    /**
     * Delete deal.
     */
    public function destroy(int $id)
    {
        $deal = Deal::findOrFail($id);
        $deal->delete();

        AuditLog::record('Deal Archived', $deal);

        return redirect()->route('admin.deals.index')
            ->with('success', 'Deal removed from pipeline.');
    }
}
