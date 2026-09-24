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
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display reporting dashboards and KPI analytics.
     */
    public function index(Request $request): Response
    {
        // 1. Property Inventory Breakdown
        $propertyTypeStats = Property::selectRaw('property_type, count(*) as count, sum(price) as total_value')
            ->groupBy('property_type')
            ->get();

        $propertyStatusStats = Property::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        // 2. Lead Source Funnel & Conversion
        $leadSourceStats = Lead::selectRaw('source, count(*) as total, sum(case when status = "converted" then 1 else 0 end) as converted')
            ->groupBy('source')
            ->get();

        // 3. Agent Performance Leaderboard
        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->get();
        $agentLeaderboard = $agents->map(function ($agent) {
            $leadsCount = Lead::where('assigned_agent_id', $agent->id)->count();
            $visitsCount = SiteVisit::where('assigned_agent_id', $agent->id)->where('status', 'completed')->count();
            $dealsClosed = Deal::where('assigned_agent_id', $agent->id)->whereHas('stage', fn ($q) => $q->where('is_won', true))->count();
            $salesVolume = Deal::where('assigned_agent_id', $agent->id)->whereHas('stage', fn ($q) => $q->where('is_won', true))->sum('expected_value');
            $commissionEarned = Commission::where('agent_id', $agent->id)->where('status', 'paid')->sum('commission_amount');

            return [
                'id' => $agent->id,
                'name' => $agent->name,
                'role' => $agent->role,
                'leads_count' => $leadsCount,
                'visits_count' => $visitsCount,
                'deals_closed' => $dealsClosed,
                'sales_volume' => (float) $salesVolume,
                'commission_earned' => (float) $commissionEarned,
            ];
        })->sortByDesc('sales_volume')->values();

        // 4. Financial Collection Summary
        $monthlyCollections = Payment::selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as month, sum(amount) as total')
            ->where('status', 'completed')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $totalCollections = (float) Payment::where('status', 'completed')->sum('amount');
        $totalBookingsValue = (float) Booking::whereIn('status', ['confirmed', 'converted'])->sum('total_amount');
        $outstandingBalance = (float) Booking::whereIn('status', ['confirmed', 'converted'])->sum('balance_amount');
        $totalCommissionsPaid = (float) Commission::where('status', 'paid')->sum('commission_amount');

        return Inertia::render('Admin/Reports/Index', [
            'propertyTypeStats' => $propertyTypeStats,
            'propertyStatusStats' => $propertyStatusStats,
            'leadSourceStats' => $leadSourceStats,
            'agentLeaderboard' => $agentLeaderboard,
            'monthlyCollections' => $monthlyCollections,
            'summary' => [
                'totalCollections' => $totalCollections,
                'totalBookingsValue' => $totalBookingsValue,
                'outstandingBalance' => $outstandingBalance,
                'totalCommissionsPaid' => $totalCommissionsPaid,
            ],
        ]);
    }

    /**
     * Export dataset as CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $type = $request->input('type', 'leads');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"export-{$type}-" . date('Y-m-d') . ".csv\"",
        ];

        return response()->stream(function () use ($type) {
            $handle = fopen('php://output', 'w');

            if ($type === 'leads') {
                fputcsv($handle, ['ID', 'Name', 'Phone', 'Email', 'Source', 'Status', 'Priority', 'Score', 'Budget Min', 'Budget Max', 'Created At']);
                Lead::with('assignedAgent')->chunk(100, function ($leads) use ($handle) {
                    foreach ($leads as $l) {
                        fputcsv($handle, [$l->id, $l->name, $l->phone, $l->email, $l->source, $l->status, $l->priority, $l->score, $l->budget_min, $l->budget_max, $l->created_at]);
                    }
                });
            } elseif ($type === 'properties') {
                fputcsv($handle, ['Code', 'Title', 'Type', 'Purpose', 'Status', 'Price', 'City', 'Bedrooms', 'Area (Sqft)', 'Agent']);
                Property::with('listingAgent')->chunk(100, function ($properties) use ($handle) {
                    foreach ($properties as $p) {
                        fputcsv($handle, [$p->property_code, $p->title, $p->property_type, $p->listing_purpose, $p->status, $p->price, $p->city, $p->bedrooms, $p->carpet_area, $p->listingAgent?->name]);
                    }
                });
            } elseif ($type === 'deals') {
                fputcsv($handle, ['Code', 'Title', 'Customer', 'Type', 'Stage', 'Expected Value', 'Actual Value', 'Close Date', 'Agent']);
                Deal::with(['customer', 'stage', 'assignedAgent'])->chunk(100, function ($deals) use ($handle) {
                    foreach ($deals as $d) {
                        fputcsv($handle, [$d->deal_code, $d->title, $d->customer?->name, $d->deal_type, $d->stage?->name, $d->expected_value, $d->actual_value, $d->expected_close_date, $d->assignedAgent?->name]);
                    }
                });
            } elseif ($type === 'payments') {
                fputcsv($handle, ['Reference', 'Receipt No', 'Customer', 'Amount', 'Type', 'Method', 'Date', 'Status']);
                Payment::with('customer')->chunk(100, function ($payments) use ($handle) {
                    foreach ($payments as $p) {
                        fputcsv($handle, [$p->payment_reference, $p->receipt_number, $p->customer?->name, $p->amount, $p->payment_type, $p->payment_method, $p->payment_date, $p->status]);
                    }
                });
            }

            fclose($handle);
        }, 200, $headers);
    }
}
