<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Deal;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommissionController extends Controller
{
    /**
     * Display commissions list and rules.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Commission::with(['agent', 'deal', 'booking.property', 'property', 'approver']);

        if ($user->isAgent()) {
            $query->where('agent_id', $user->id);
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('agent_id') && $request->input('agent_id') !== 'all') {
            $query->where('agent_id', $request->input('agent_id'));
        }

        $commissions = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total_commissions' => (float) Commission::sum('commission_amount'),
            'pending' => (float) Commission::where('status', 'pending')->sum('commission_amount'),
            'approved' => (float) Commission::where('status', 'approved')->sum('commission_amount'),
            'paid' => (float) Commission::where('status', 'paid')->sum('commission_amount'),
        ];

        $rules = CommissionRule::latest()->get();
        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->select('id', 'name', 'commission_rate')->get();
        $deals = Deal::whereHas('stage', fn ($q) => $q->where('is_won', true))->orWhereNull('closed_at')->select('id', 'title', 'deal_code', 'expected_value', 'property_id')->get();
        $bookings = Booking::select('id', 'booking_number', 'total_amount', 'property_id')->get();

        return Inertia::render('Admin/Commissions/Index', [
            'commissions' => $commissions,
            'rules' => $rules,
            'stats' => $stats,
            'agents' => $agents,
            'deals' => $deals,
            'bookings' => $bookings,
            'filters' => $request->only(['status', 'agent_id']),
        ]);
    }

    /**
     * Store calculated commission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
            'deal_id' => 'nullable|exists:deals,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'property_id' => 'nullable|exists:properties,id',
            'commission_type' => 'required|in:percentage,fixed',
            'commission_rate' => 'required|numeric|min:0',
            'base_amount' => 'required|numeric|min:0',
            'payout_notes' => 'nullable|string',
        ]);

        $commissionAmount = $validated['commission_type'] === 'percentage'
            ? ($validated['base_amount'] * ($validated['commission_rate'] / 100))
            : $validated['commission_rate'];

        $commission = Commission::create([
            'tenant_id' => $request->user()->tenant_id,
            'agent_id' => $validated['agent_id'],
            'deal_id' => $validated['deal_id'] ?? null,
            'booking_id' => $validated['booking_id'] ?? null,
            'property_id' => $validated['property_id'] ?? null,
            'commission_type' => $validated['commission_type'],
            'commission_rate' => $validated['commission_rate'],
            'base_amount' => $validated['base_amount'],
            'commission_amount' => $commissionAmount,
            'status' => 'pending',
            'payout_notes' => $validated['payout_notes'] ?? null,
        ]);

        AuditLog::record("Commission #{$commission->id} for \${$commissionAmount} Calculated for Agent #{$validated['agent_id']}", $commission);

        return back()->with('success', "Commission of \${$commissionAmount} calculated and registered.");
    }

    /**
     * Approve commission payout.
     */
    public function approve(Request $request, int $id)
    {
        $commission = Commission::findOrFail($id);
        $commission->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        AuditLog::record("Commission #{$commission->id} Approved", $commission);

        return back()->with('success', 'Commission payout approved.');
    }

    /**
     * Mark commission as paid.
     */
    public function markPaid(Request $request, int $id)
    {
        $commission = Commission::findOrFail($id);
        $commission->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payout_notes' => $request->input('notes', $commission->payout_notes),
        ]);

        AuditLog::record("Commission #{$commission->id} Paid", $commission);

        return back()->with('success', 'Commission payout recorded as paid.');
    }

    /**
     * Save new commission rule.
     */
    public function storeRule(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'property_type' => 'required|string',
            'deal_type' => 'required|in:sale,rent,lease,all',
            'commission_type' => 'required|in:percentage,fixed',
            'rate' => 'required|numeric|min:0',
        ]);

        CommissionRule::create([
            'tenant_id' => $request->user()->tenant_id,
            ...$validated,
            'is_active' => true,
        ]);

        return back()->with('success', 'Commission rule created.');
    }
}
