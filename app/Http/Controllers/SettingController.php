<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Tenant;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function company(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $tenant = Tenant::findOrFail($tenantId);
        $company = Company::where('tenant_id', $tenantId)->first();
        $branches = Branch::where('tenant_id', $tenantId)->get();

        return view('settings.company', compact('tenant', 'company', 'branches'));
    }

    public function updateCompany(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $company = Company::where('tenant_id', $tenantId)->firstOrFail();

        $validated = $request->validate([
            'legal_name' => 'required|string|max:150',
            'trade_name' => 'required|string|max:150',
            'gstin' => 'nullable|string|max:20',
            'phone' => 'required|string|max:25',
            'email' => 'required|email|max:150',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
        ]);

        $company->update($validated);

        return redirect()->back()->with('success', 'Company profile details updated.');
    }

    public function storeBranch(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $company = Company::where('tenant_id', $tenantId)->first();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:30',
            'phone' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        Branch::create([
            'tenant_id' => $tenantId,
            'company_id' => $company?->id,
            'is_main_branch' => false,
            'is_active' => true,
            ...$validated,
        ]);

        return redirect()->back()->with('success', "Branch {$validated['name']} created.");
    }
}
