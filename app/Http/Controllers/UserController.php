<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Instructor;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of staff and system users.
     */
    public function index(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        $search = $request->query('search');
        $roleFilter = $request->query('role');
        $branchFilter = $request->query('branch');
        $statusFilter = $request->query('status');

        $query = User::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->with(['branch', 'branches', 'roles'])
            ->latest('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($roleFilter) {
            $query->where('role_slug', $roleFilter);
        }

        if ($branchFilter) {
            $query->where(function ($q) use ($branchFilter) {
                $q->where('branch_id', $branchFilter)
                    ->orWhereHas('branches', function ($bq) use ($branchFilter) {
                        $bq->where('branches.id', $branchFilter);
                    });
            });
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('is_active', (bool)$statusFilter);
        }

        $users = $query->paginate(15)->withQueryString();

        // Telemetry KPI Stats for tenant
        $baseTenantQuery = User::withoutGlobalScopes()->where('tenant_id', $tenantId);
        $totalUsers = (clone $baseTenantQuery)->count();
        $activeUsers = (clone $baseTenantQuery)->where('is_active', true)->count();
        $instructorCount = (clone $baseTenantQuery)->where('role_slug', 'instructor')->count();
        $adminCount = (clone $baseTenantQuery)->whereIn('role_slug', ['business_owner', 'school_admin', 'branch_manager'])->count();

        $roles = Role::orderBy('name')->get();
        $branches = Branch::where('tenant_id', $tenantId)->orderBy('name')->get();

        return view('users.index', compact(
            'users',
            'roles',
            'branches',
            'totalUsers',
            'activeUsers',
            'instructorCount',
            'adminCount',
            'search',
            'roleFilter',
            'branchFilter',
            'statusFilter'
        ));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role_slug' => ['required', 'string', 'exists:roles,slug'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'branches' => ['nullable', 'array'],
            'branches.*' => ['exists:branches,id'],
            'password' => ['required', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
            // Optional instructor specific fields if role is instructor
            'license_number' => ['nullable', 'string', 'max:50'],
            'vehicle_types' => ['nullable', 'array'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::beginTransaction();
        try {
            $user = new User();
            $user->tenant_id = $tenantId;
            $user->branch_id = $validated['branch_id'] ?? null;
            $user->name = $validated['name'];
            $user->email = strtolower(trim($validated['email']));
            $user->phone = $validated['phone'] ?? null;
            $user->role_slug = $validated['role_slug'];
            $user->password = Hash::make($validated['password']);
            $user->is_active = $request->boolean('is_active', true);
            $user->save();

            // Link roles relationship
            $role = Role::where('slug', $validated['role_slug'])->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }

            // Sync assigned branches
            $branchIds = $request->input('branches', []);
            if (!empty($validated['branch_id']) && !in_array($validated['branch_id'], $branchIds)) {
                $branchIds[] = $validated['branch_id'];
            }
            if (!empty($branchIds)) {
                $user->branches()->sync($branchIds);
            }

            // Auto-create Instructor record if role is instructor
            if ($user->role_slug === 'instructor') {
                $instructorCode = 'INS-' . strtoupper(substr($user->name, 0, 3)) . '-' . rand(100, 999);
                Instructor::create([
                    'tenant_id' => $tenantId,
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                    'instructor_code' => $instructorCode,
                    'name' => $user->name,
                    'phone' => $user->phone ?? '9000000000',
                    'email' => $user->email,
                    'license_number' => $request->input('license_number', 'DL-' . strtoupper(substr(uniqid(), 0, 8))),
                    'license_expiry_date' => now()->addYears(3),
                    'experience_years' => 3,
                    'vehicle_types' => $request->input('vehicle_types', ['LMV-Car', '2-Wheeler']),
                    'status' => 'active',
                    'hourly_rate' => $request->input('hourly_rate', 250.00),
                    'rating' => 5.0,
                ]);
            }

            // Log activity
            ActivityLog::create([
                'tenant_id' => $tenantId,
                'user_id' => auth()->id(),
                'module' => 'users',
                'action' => 'create',
                'description' => "Created staff account for {$user->name} ({$user->role_slug})",
                'ip_address' => $request->ip(),
                'properties' => [
                    'created_user_id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role_slug,
                ],
            ]);

            DB::commit();

            return redirect()->route('users.index')->with('success', "User '{$user->name}' created successfully with role " . ucwords(str_replace('_', ' ', $user->role_slug)) . ".");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;

        // Ensure user belongs to same tenant
        if ($user->tenant_id != $tenantId && !auth()->user()?->isSuperAdmin()) {
            abort(403, 'Unauthorized access to user.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role_slug' => ['required', 'string', 'exists:roles,slug'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'branches' => ['nullable', 'array'],
            'branches.*' => ['exists:branches,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            $user->name = $validated['name'];
            $user->email = strtolower(trim($validated['email']));
            $user->phone = $validated['phone'] ?? null;
            $user->role_slug = $validated['role_slug'];
            $user->branch_id = $validated['branch_id'] ?? null;
            $user->is_active = $request->boolean('is_active', true);
            $user->save();

            // Sync role
            $role = Role::where('slug', $validated['role_slug'])->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }

            // Sync multi-branches
            $branchIds = $request->input('branches', []);
            if (!empty($validated['branch_id']) && !in_array($validated['branch_id'], $branchIds)) {
                $branchIds[] = $validated['branch_id'];
            }
            $user->branches()->sync($branchIds);

            // Sync name/phone with Instructor record if exists
            if ($user->instructor) {
                $user->instructor->update([
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? $user->instructor->phone,
                    'branch_id' => $user->branch_id,
                ]);
            } elseif ($user->role_slug === 'instructor') {
                $instructorCode = 'INS-' . strtoupper(substr($user->name, 0, 3)) . '-' . rand(100, 999);
                Instructor::create([
                    'tenant_id' => $tenantId,
                    'branch_id' => $user->branch_id,
                    'user_id' => $user->id,
                    'instructor_code' => $instructorCode,
                    'name' => $user->name,
                    'phone' => $user->phone ?? '9000000000',
                    'email' => $user->email,
                    'license_number' => 'DL-' . strtoupper(substr(uniqid(), 0, 8)),
                    'license_expiry_date' => now()->addYears(3),
                    'experience_years' => 2,
                    'vehicle_types' => ['LMV-Car'],
                    'status' => 'active',
                    'hourly_rate' => 250.00,
                    'rating' => 5.0,
                ]);
            }

            // Log update
            ActivityLog::create([
                'tenant_id' => $tenantId,
                'user_id' => auth()->id(),
                'module' => 'users',
                'action' => 'update',
                'description' => "Updated user profile for {$user->name} ({$user->role_slug})",
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return redirect()->route('users.index')->with('success', "User '{$user->name}' updated successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user status (active / disabled).
     */
    public function toggleStatus(Request $request, User $user)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        if ($user->tenant_id != $tenantId && !auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot disable your own active account.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'activated' : 'disabled';
        return back()->with('success', "User {$user->name} has been {$statusText}.");
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        if ($user->tenant_id != $tenantId && !auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user->password = Hash::make($request->input('password'));
        $user->save();

        ActivityLog::create([
            'tenant_id' => $tenantId,
            'user_id' => auth()->id(),
            'module' => 'users',
            'action' => 'reset_password',
            'description' => "Reset password for user {$user->name} ({$user->email})",
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Password for {$user->name} reset successfully.");
    }
}
