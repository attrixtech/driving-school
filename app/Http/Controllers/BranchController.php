<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Display a listing of all branches with operational metrics.
     */
    public function index(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $search = $request->query('search');
        $statusFilter = $request->query('status');

        $query = Branch::where('tenant_id', $tenantId)
            ->withCount(['students', 'instructors', 'vehicles', 'admissions'])
            ->orderByDesc('is_main_branch')
            ->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('is_active', (bool)$statusFilter);
        }

        $branches = $query->get();

        // Telemetry KPI Stats
        $totalBranches = $branches->count();
        $activeBranches = $branches->where('is_active', true)->count();
        $totalInstructors = Instructor::where('tenant_id', $tenantId)->count();
        $totalVehicles = Vehicle::where('tenant_id', $tenantId)->count();
        $totalStudents = Student::where('tenant_id', $tenantId)->count();

        $activeBranchId = session('active_branch_id', auth()->user()?->branch_id);

        return view('branches.index', compact(
            'branches',
            'totalBranches',
            'activeBranches',
            'totalInstructors',
            'totalVehicles',
            'totalStudents',
            'activeBranchId',
            'search',
            'statusFilter'
        ));
    }

    /**
     * Store a newly created branch.
     */
    public function store(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $company = Company::where('tenant_id', $tenantId)->first();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:30', Rule::unique('branches', 'code')->where('tenant_id', $tenantId)],
            'phone' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_main_branch' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            $isMain = $request->boolean('is_main_branch', false);

            // If marked as main branch, reset other branches
            if ($isMain) {
                Branch::where('tenant_id', $tenantId)->update(['is_main_branch' => false]);
            }

            // If this is the very first branch, make it main branch automatically
            $hasBranches = Branch::where('tenant_id', $tenantId)->exists();
            if (!$hasBranches) {
                $isMain = true;
            }

            $branch = Branch::create([
                'tenant_id' => $tenantId,
                'company_id' => $company?->id,
                'name' => $validated['name'],
                'code' => strtoupper(trim($validated['code'])),
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postal_code' => $validated['postal_code'] ?? null,
                'is_main_branch' => $isMain,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Automatically grant branch access to the creating user
            if (auth()->check()) {
                auth()->user()->branches()->syncWithoutDetaching([$branch->id]);
            }

            ActivityLog::create([
                'tenant_id' => $tenantId,
                'user_id' => auth()->id(),
                'module' => 'branches',
                'action' => 'create',
                'description' => "Created new operating branch '{$branch->name}' ({$branch->code})",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return redirect()->route('branches.index')
                ->with('success', "Branch '{$branch->name}' ({$branch->code}) created successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create branch: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified branch.
     */
    public function update(Request $request, Branch $branch)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        if ($branch->tenant_id != $tenantId && !auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:30', Rule::unique('branches', 'code')->where('tenant_id', $tenantId)->ignore($branch->id)],
            'phone' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'is_main_branch' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            $isMain = $request->boolean('is_main_branch', false);

            if ($isMain && !$branch->is_main_branch) {
                Branch::where('tenant_id', $tenantId)->where('id', '!=', $branch->id)->update(['is_main_branch' => false]);
            }

            $branch->name = $validated['name'];
            $branch->code = strtoupper(trim($validated['code']));
            $branch->phone = $validated['phone'] ?? null;
            $branch->email = $validated['email'] ?? null;
            $branch->address = $validated['address'] ?? null;
            $branch->city = $validated['city'];
            $branch->state = $validated['state'];
            $branch->postal_code = $validated['postal_code'] ?? null;
            $branch->is_main_branch = $isMain;
            $branch->is_active = $request->boolean('is_active', true);
            $branch->save();

            // If active branch in session was this one, update session name
            if (session('active_branch_id') == $branch->id) {
                session(['active_branch_name' => $branch->name]);
            }

            ActivityLog::create([
                'tenant_id' => $tenantId,
                'user_id' => auth()->id(),
                'module' => 'branches',
                'action' => 'update',
                'description' => "Updated branch details for '{$branch->name}' ({$branch->code})",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return redirect()->route('branches.index')
                ->with('success', "Branch '{$branch->name}' updated successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update branch: ' . $e->getMessage());
        }
    }

    /**
     * Toggle branch active status.
     */
    public function toggleStatus(Request $request, Branch $branch)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        if ($branch->tenant_id != $tenantId && !auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        if ($branch->is_main_branch && $branch->is_active) {
            return back()->with('error', 'Cannot deactivate the primary headquarters branch. Designate another main branch first.');
        }

        $branch->is_active = !$branch->is_active;
        $branch->save();

        $status = $branch->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Branch '{$branch->name}' has been {$status}.");
    }

    /**
     * Set a branch as the primary headquarters.
     */
    public function setMain(Request $request, Branch $branch)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        if ($branch->tenant_id != $tenantId && !auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        DB::transaction(function () use ($tenantId, $branch) {
            Branch::where('tenant_id', $tenantId)->update(['is_main_branch' => false]);
            $branch->is_main_branch = true;
            $branch->is_active = true;
            $branch->save();
        });

        return back()->with('success', "Branch '{$branch->name}' is now set as the primary Headquarters.");
    }
}
