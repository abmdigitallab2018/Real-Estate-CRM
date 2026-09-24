<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Commission;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Property;
use App\Models\SiteVisit;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // If Super Admin without tenant, redirect to SaaS Superadmin dashboard
        if ($user->role === 'super_admin' && empty($user->tenant_id)) {
            return redirect()->route('superadmin.dashboard');
        }

        // Date range filter: 'all', 'today', 'this_week', 'this_month', 'this_quarter', 'this_year'
        $dateFilter = $request->input('period', 'this_month');
        $startDate = match ($dateFilter) {
            'today' => Carbon::today(),
            'this_week' => Carbon::now()->startOfWeek(),
            'this_month' => Carbon::now()->startOfMonth(),
            'this_quarter' => Carbon::now()->startOfQuarter(),
            'this_year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        // 1. Property Statistics
        $propertyQuery = Property::query();
        if ($user->isAgent()) {
            // agents can see their own properties or all available
        }
        $totalProperties = (clone $propertyQuery)->count();
        $availableProperties = (clone $propertyQuery)->where('status', 'available')->count();
        $reservedProperties = (clone $propertyQuery)->where('status', 'reserved')->count();
        $soldProperties = (clone $propertyQuery)->where('status', 'sold')->count();
        $rentedProperties = (clone $propertyQuery)->whereIn('status', ['rented', 'leased'])->count();
        $underNegotiationProperties = (clone $propertyQuery)->where('status', 'under_negotiation')->count();

        // 2. Leads Statistics
        $leadQuery = Lead::query();
        if ($user->isAgent()) {
            $leadQuery->where('assigned_agent_id', $user->id);
        }
        $totalLeads = (clone $leadQuery)->count();
        $newLeads = (clone $leadQuery)->where('status', 'new')->count();
        $qualifiedLeads = (clone $leadQuery)->where('status', 'qualified')->count();
        $convertedLeads = (clone $leadQuery)->where('status', 'converted')->count();
        $lostLeads = (clone $leadQuery)->where('status', 'lost')->count();
        $leadConversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        // 3. Deals & Pipeline Statistics
        $dealQuery = Deal::query();
        if ($user->isAgent()) {
            $dealQuery->where('assigned_agent_id', $user->id);
        }
        $activeDealsCount = (clone $dealQuery)->whereNull('closed_at')->count();
        $activeDealsValue = (clone $dealQuery)->whereNull('closed_at')->sum('expected_value');
        $closedWonCount = (clone $dealQuery)->whereHas('stage', fn ($q) => $q->where('is_won', true))->count();
        $closedWonValue = (clone $dealQuery)->whereHas('stage', fn ($q) => $q->where('is_won', true))->sum('expected_value');

        // 4. Financials (Payments collected & Commissions)
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $pendingCommissions = Commission::where('status', 'pending')->sum('commission_amount');
        $paidCommissions = Commission::where('status', 'paid')->sum('commission_amount');

        // 5. Today's Site Visits
        $todayVisitsQuery = SiteVisit::with(['property', 'customer', 'assignedAgent'])
            ->whereDate('scheduled_date', Carbon::today())
            ->orderBy('scheduled_time');
        if ($user->isAgent()) {
            $todayVisitsQuery->where('assigned_agent_id', $user->id);
        }
        $todaySiteVisits = $todayVisitsQuery->get();

        // 6. Upcoming Follow-up Tasks & Overdue Tasks
        $taskQuery = Task::with(['assignedUser', 'customer', 'property'])
            ->where('status', '!=', 'completed');
        if ($user->isAgent()) {
            $taskQuery->where('assigned_to', $user->id);
        }
        $overdueTasks = (clone $taskQuery)->whereDate('due_date', '<', Carbon::today())->orderBy('due_date')->take(5)->get();
        $todayTasks = (clone $taskQuery)->whereDate('due_date', Carbon::today())->orderBy('due_time')->take(5)->get();
        $upcomingTasks = (clone $taskQuery)->whereDate('due_date', '>', Carbon::today())->orderBy('due_date')->take(5)->get();

        // 7. Agent Performance Summary (for managers/admins)
        $agentPerformance = [];
        if (!$user->isAgent() && !$user->isCustomer()) {
            $agents = User::where('tenant_id', $user->tenant_id)->whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->get();
            foreach ($agents as $agent) {
                $agentLeads = Lead::where('assigned_agent_id', $agent->id)->count();
                $agentVisits = SiteVisit::where('assigned_agent_id', $agent->id)->where('status', 'completed')->count();
                $agentDeals = Deal::where('assigned_agent_id', $agent->id)->whereHas('stage', fn ($q) => $q->where('is_won', true))->sum('expected_value');
                $agentCommission = Commission::where('agent_id', $agent->id)->sum('commission_amount');

                $agentPerformance[] = [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'email' => $agent->email,
                    'role' => $agent->role,
                    'leads_count' => $agentLeads,
                    'completed_visits' => $agentVisits,
                    'closed_volume' => (float) $agentDeals,
                    'target_amount' => (float) $agent->target_amount,
                    'commission_earned' => (float) $agentCommission,
                ];
            }
        }

        // 8. Lead Source Breakdown
        $leadSources = Lead::selectRaw('source, count(*) as count')
            ->groupBy('source')
            ->pluck('count', 'source');

        // 9. Recent Leads & Deals
        $recentLeads = (clone $leadQuery)->with('assignedAgent')->latest()->take(5)->get();
        $recentDeals = (clone $dealQuery)->with(['stage', 'customer', 'property', 'assignedAgent'])->latest()->take(5)->get();

        return Inertia::render('Admin/Dashboard', [
            'metrics' => [
                'properties' => [
                    'total' => $totalProperties,
                    'available' => $availableProperties,
                    'reserved' => $reservedProperties,
                    'sold' => $soldProperties,
                    'rented' => $rentedProperties,
                    'under_negotiation' => $underNegotiationProperties,
                ],
                'leads' => [
                    'total' => $totalLeads,
                    'new' => $newLeads,
                    'qualified' => $qualifiedLeads,
                    'converted' => $convertedLeads,
                    'lost' => $lostLeads,
                    'conversion_rate' => $leadConversionRate,
                ],
                'deals' => [
                    'active_count' => $activeDealsCount,
                    'active_value' => (float) $activeDealsValue,
                    'closed_won_count' => $closedWonCount,
                    'closed_won_value' => (float) $closedWonValue,
                ],
                'financials' => [
                    'total_collected' => (float) $totalRevenue,
                    'pending_commissions' => (float) $pendingCommissions,
                    'paid_commissions' => (float) $paidCommissions,
                ],
            ],
            'todaySiteVisits' => $todaySiteVisits,
            'overdueTasks' => $overdueTasks,
            'todayTasks' => $todayTasks,
            'upcomingTasks' => $upcomingTasks,
            'agentPerformance' => $agentPerformance,
            'leadSources' => $leadSources,
            'recentLeads' => $recentLeads,
            'recentDeals' => $recentDeals,
            'currentPeriod' => $dateFilter,
        ]);
    }
}
