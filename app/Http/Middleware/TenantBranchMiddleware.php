<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantBranchMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // 1. Resolve Tenant
        if ($user->tenant_id) {
            $tenant = Tenant::find($user->tenant_id);
            if ($tenant && $tenant->status === 'active') {
                session(['active_tenant_id' => $tenant->id]);
                session(['active_tenant_name' => $tenant->name]);
                app()->instance('tenant', $tenant);
            }
        }

        // 2. Resolve Active Branch
        $activeBranchId = session('active_branch_id');
        if (!$activeBranchId && $user->branch_id) {
            $activeBranchId = $user->branch_id;
            session(['active_branch_id' => $activeBranchId]);
        }

        if ($activeBranchId) {
            $branch = Branch::find($activeBranchId);
            if ($branch) {
                session(['active_branch_name' => $branch->name]);
                app()->instance('branch', $branch);
            }
        }

        // Pass global view shares
        view()->share('currentTenant', app()->bound('tenant') ? app('tenant') : null);
        view()->share('currentBranch', app()->bound('branch') ? app('branch') : null);

        return $next($request);
    }
}
