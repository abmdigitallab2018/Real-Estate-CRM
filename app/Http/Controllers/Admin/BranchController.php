<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    /**
     * Display agency branch offices.
     */
    public function index(): Response
    {
        $branches = Branch::withCount(['users', 'properties'])->latest()->get();

        return Inertia::render('Admin/Branches/Index', [
            'branches' => $branches,
        ]);
    }

    /**
     * Store new branch office.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'is_main' => 'boolean',
        ]);

        $validated['status'] = 'active';

        $branch = Branch::create($validated);

        AuditLog::record("Branch Office '{$branch->name}' Created", $branch);

        return back()->with('success', "Branch '{$branch->name}' created.");
    }

    /**
     * Update branch.
     */
    public function update(Request $request, int $id)
    {
        $branch = Branch::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'is_main' => 'boolean',
            'status' => 'required|in:active,inactive',
        ]);

        $branch->update($validated);

        return back()->with('success', 'Branch details updated.');
    }

    /**
     * Delete branch.
     */
    public function destroy(int $id)
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return back()->with('success', 'Branch office removed.');
    }
}
