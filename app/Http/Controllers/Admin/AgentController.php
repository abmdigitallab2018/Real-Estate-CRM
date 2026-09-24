<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Commission;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Property;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AgentController extends Controller
{
    /**
     * Display team & agent management directory.
     */
    public function index(Request $request): Response
    {
        $query = User::with('branch')
            ->whereIn('role', ['agent', 'sales_manager', 'property_manager', 'accountant', 'agency_admin']);

        if ($request->filled('role') && $request->input('role') !== 'all') {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('branch_id') && $request->input('branch_id') !== 'all') {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        // Compute performance stats for each user
        $users->getCollection()->transform(function ($u) {
            $u->active_leads = Lead::where('assigned_agent_id', $u->id)->whereNotIn('status', ['converted', 'lost'])->count();
            $u->active_deals = Deal::where('assigned_agent_id', $u->id)->whereNull('closed_at')->count();
            $u->closed_sales_volume = (float) Deal::where('assigned_agent_id', $u->id)->whereHas('stage', fn ($q) => $q->where('is_won', true))->sum('expected_value');
            $u->total_commissions = (float) Commission::where('agent_id', $u->id)->sum('commission_amount');
            $u->completed_visits = SiteVisit::where('assigned_agent_id', $u->id)->where('status', 'completed')->count();
            return $u;
        });

        $branches = Branch::where('status', 'active')->select('id', 'name')->get();

        return Inertia::render('Admin/Agents/Index', [
            'agents' => $users,
            'branches' => $branches,
            'filters' => $request->only(['role', 'branch_id', 'search']),
        ]);
    }

    /**
     * Store new team member.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:agency_admin,sales_manager,agent,property_manager,accountant',
            'branch_id' => 'nullable|exists:branches,id',
            'license_number' => 'nullable|string|max:100',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'specialization' => 'nullable|string|max:255',
            'target_amount' => 'nullable|numeric|min:0',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'active';

        $user = User::create($validated);

        AuditLog::record("Team Member '{$user->name}' ({$user->role}) Added", $user);

        return back()->with('success', "Team member '{$user->name}' added successfully.");
    }

    /**
     * Update team member.
     */
    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|max:255|unique:users,email,{$id}",
            'phone' => 'nullable|string|max:50',
            'role' => 'required|in:agency_admin,sales_manager,agent,property_manager,accountant',
            'branch_id' => 'nullable|exists:branches,id',
            'license_number' => 'nullable|string|max:100',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'specialization' => 'nullable|string|max:255',
            'target_amount' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,suspended',
            'password' => 'nullable|string|min:8',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        AuditLog::record("Team Member '{$user->name}' Updated", $user);

        return back()->with('success', "Team member '{$user->name}' updated.");
    }

    /**
     * Deactivate / delete user.
     */
    public function destroy(int $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', 'User removed.');
    }
}
