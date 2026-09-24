<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Property;
use App\Models\SiteVisit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SiteVisitController extends Controller
{
    /**
     * Display site visits schedule & calendar.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = SiteVisit::with(['property', 'customer', 'assignedAgent']);

        if ($user->isAgent()) {
            $query->where('assigned_agent_id', $user->id);
        }

        // View mode filter: 'today', 'upcoming', 'past', 'all'
        $view = $request->input('view', 'all');
        if ($view === 'today') {
            $query->whereDate('scheduled_date', Carbon::today());
        } elseif ($view === 'upcoming') {
            $query->whereDate('scheduled_date', '>=', Carbon::today())->whereIn('status', ['scheduled', 'confirmed']);
        } elseif ($view === 'past') {
            $query->whereDate('scheduled_date', '<', Carbon::today());
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('agent_id') && $request->input('agent_id') !== 'all') {
            $query->where('assigned_agent_id', $request->input('agent_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_date', $request->input('date'));
        }

        $visits = $query->orderBy('scheduled_date', 'asc')->orderBy('scheduled_time', 'asc')->paginate(15)->withQueryString();

        $stats = [
            'total' => SiteVisit::count(),
            'today' => SiteVisit::whereDate('scheduled_date', Carbon::today())->count(),
            'upcoming' => SiteVisit::whereDate('scheduled_date', '>=', Carbon::today())->whereIn('status', ['scheduled', 'confirmed'])->count(),
            'completed' => SiteVisit::where('status', 'completed')->count(),
            'cancelled' => SiteVisit::where('status', 'cancelled')->count(),
        ];

        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->select('id', 'name')->get();
        $properties = Property::whereIn('status', ['available', 'under_negotiation', 'reserved'])->select('id', 'title', 'property_code')->get();
        $customers = Customer::where('status', 'active')->select('id', 'name', 'phone')->get();

        return Inertia::render('Admin/SiteVisits/Index', [
            'visits' => $visits,
            'stats' => $stats,
            'agents' => $agents,
            'properties' => $properties,
            'customers' => $customers,
            'filters' => $request->only(['view', 'status', 'agent_id', 'date']),
        ]);
    }

    /**
     * Store new site visit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'customer_id' => 'required|exists:customers,id',
            'assigned_agent_id' => 'required|exists:users,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required',
            'agent_notes' => 'nullable|string',
        ]);

        $count = SiteVisit::count() + 1;
        $validated['visit_code'] = 'VST-' . date('Y') . '-' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
        $validated['status'] = 'scheduled';

        $visit = SiteVisit::create($validated);

        AuditLog::record('Site Visit Scheduled', $visit, null, $visit->toArray());

        return back()->with('success', "Site visit {$visit->visit_code} scheduled for " . Carbon::parse($visit->scheduled_date)->format('M d, Y') . " at {$visit->scheduled_time}.");
    }

    /**
     * Update visit status, feedback, outcome.
     */
    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,confirmed,completed,cancelled,no_show',
            'interest_level' => 'nullable|in:low,medium,high,very_high',
            'customer_feedback' => 'nullable|string|max:1000',
            'agent_notes' => 'nullable|string|max:1000',
            'next_action' => 'nullable|string|max:255',
        ]);

        $visit = SiteVisit::findOrFail($id);
        $oldStatus = $visit->status;
        $visit->update($validated);

        AuditLog::record("Site Visit {$visit->visit_code} status updated from {$oldStatus} to {$validated['status']}", $visit);

        return back()->with('success', "Visit status updated to '{$validated['status']}'.");
    }

    /**
     * Reschedule visit.
     */
    public function reschedule(Request $request, int $id)
    {
        $validated = $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'required',
            'agent_notes' => 'nullable|string',
        ]);

        $visit = SiteVisit::findOrFail($id);
        $visit->update([
            'scheduled_date' => $validated['scheduled_date'],
            'scheduled_time' => $validated['scheduled_time'],
            'status' => 'confirmed',
            'agent_notes' => $validated['agent_notes'] ?? $visit->agent_notes,
        ]);

        AuditLog::record("Site Visit {$visit->visit_code} rescheduled to {$validated['scheduled_date']} {$validated['scheduled_time']}", $visit);

        return back()->with('success', "Site visit rescheduled successfully.");
    }

    /**
     * Cancel site visit.
     */
    public function destroy(int $id)
    {
        $visit = SiteVisit::findOrFail($id);
        $visit->update(['status' => 'cancelled']);

        AuditLog::record("Site Visit {$visit->visit_code} cancelled", $visit);

        return back()->with('success', 'Site visit cancelled.');
    }
}
