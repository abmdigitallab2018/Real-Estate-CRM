<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    /**
     * Display tasks & activities agenda.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Task::with(['assignedUser', 'creator', 'customer', 'property', 'deal']);

        if ($user->isAgent()) {
            $query->where('assigned_to', $user->id);
        }

        // Filter by tab: 'all', 'today', 'overdue', 'upcoming', 'completed'
        $view = $request->input('view', 'pending');
        if ($view === 'today') {
            $query->whereDate('due_date', Carbon::today())->where('status', '!=', 'completed');
        } elseif ($view === 'overdue') {
            $query->whereDate('due_date', '<', Carbon::today())->where('status', '!=', 'completed');
        } elseif ($view === 'upcoming') {
            $query->whereDate('due_date', '>', Carbon::today())->where('status', '!=', 'completed');
        } elseif ($view === 'completed') {
            $query->where('status', 'completed');
        } elseif ($view === 'pending') {
            $query->where('status', '!=', 'completed');
        }

        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('activity_type', $request->input('type'));
        }

        if ($request->filled('priority') && $request->input('priority') !== 'all') {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('agent_id') && $request->input('agent_id') !== 'all') {
            $query->where('assigned_to', $request->input('agent_id'));
        }

        $tasks = $query->orderBy('due_date', 'asc')->orderBy('priority', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'pending' => Task::where('status', '!=', 'completed')->count(),
            'today' => Task::whereDate('due_date', Carbon::today())->where('status', '!=', 'completed')->count(),
            'overdue' => Task::whereDate('due_date', '<', Carbon::today())->where('status', '!=', 'completed')->count(),
            'completed' => Task::where('status', 'completed')->count(),
        ];

        $agents = User::whereIn('role', ['agent', 'sales_manager', 'agency_admin'])->select('id', 'name')->get();
        $customers = Customer::where('status', 'active')->select('id', 'name', 'phone')->get();
        $properties = Property::select('id', 'title', 'property_code')->get();

        return Inertia::render('Admin/Tasks/Index', [
            'tasks' => $tasks,
            'stats' => $stats,
            'agents' => $agents,
            'customers' => $customers,
            'properties' => $properties,
            'filters' => $request->only(['view', 'type', 'priority', 'agent_id']),
        ]);
    }

    /**
     * Store new task or follow-up.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'activity_type' => 'required|in:call,meeting,site_visit,email,whatsapp,follow_up,document_prep',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'due_time' => 'nullable',
            'priority' => 'required|in:low,medium,high,urgent',
            'customer_id' => 'nullable|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'property_id' => 'nullable|exists:properties,id',
            'deal_id' => 'nullable|exists:deals,id',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['status'] = 'pending';

        $task = Task::create($validated);

        AuditLog::record('Activity Scheduled', $task, null, $task->toArray());

        return back()->with('success', "Follow-up activity '{$task->subject}' scheduled.");
    }

    /**
     * Toggle task complete status.
     */
    public function toggleComplete(Request $request, int $id)
    {
        $task = Task::findOrFail($id);
        $newStatus = $task->status === 'completed' ? 'pending' : 'completed';

        $task->update([
            'status' => $newStatus,
            'completed_at' => $newStatus === 'completed' ? now() : null,
        ]);

        AuditLog::record("Task '{$task->subject}' marked as {$newStatus}", $task);

        return back()->with('success', $newStatus === 'completed' ? 'Task marked as completed!' : 'Task reopened.');
    }

    /**
     * Delete task.
     */
    public function destroy(int $id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return back()->with('success', 'Task removed.');
    }
}
