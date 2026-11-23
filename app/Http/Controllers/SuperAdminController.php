<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Branch;
use App\Models\Student;
use App\Models\Vehicle;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    public function tenants(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $tenants = Tenant::withCount(['branches', 'users', 'students', 'vehicles'])
            ->when($status, fn($q) => $q->where('subscription_status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15);

        // Platform-wide aggregation metrics
        $metrics = [
            'total_tenants' => Tenant::count(),
            'active_subscriptions' => Tenant::where('subscription_status', 'active')->count(),
            'trial_tenants' => Tenant::where('subscription_status', 'trial')->count(),
            'total_students_across_platform' => Student::withoutGlobalScopes()->count(),
            'total_vehicles_managed' => Vehicle::withoutGlobalScopes()->count(),
            'total_platform_invoiced' => Invoice::withoutGlobalScopes()->sum('total_amount'),
        ];

        return view('superadmin.tenants', compact('tenants', 'metrics', 'search', 'status'));
    }

    public function storeTenant(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'contact_person' => 'required|string|max:100',
            'email' => 'required|email|unique:tenants,email',
            'phone' => 'required|string|max:20',
            'subscription_plan' => 'required|in:Starter,Professional,Enterprise,Multi-Branch Fleet',
            'subscription_status' => 'required|in:active,trial,expired,suspended',
            'max_branches' => 'required|integer|min:1',
            'max_students' => 'required|integer|min:10',
            'max_vehicles' => 'required|integer|min:1',
        ]);

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . rand(100, 999),
            'contact_person' => $validated['contact_person'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'status' => 'active',
            'subscription_plan' => $validated['subscription_plan'],
            'subscription_status' => $validated['subscription_status'],
            'subscription_ends_at' => now()->addYear(),
            'max_branches' => $validated['max_branches'],
            'max_students' => $validated['max_students'],
            'max_vehicles' => $validated['max_vehicles'],
        ]);

        return redirect()->back()->with('success', "Tenant academy '{$tenant->name}' created successfully with {$tenant->subscription_plan} plan.");
    }

    public function updateSubscription(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'subscription_plan' => 'required|in:Starter,Professional,Enterprise,Multi-Branch Fleet',
            'subscription_status' => 'required|in:active,trial,expired,suspended',
            'max_branches' => 'required|integer|min:1',
            'max_students' => 'required|integer|min:10',
            'max_vehicles' => 'required|integer|min:1',
        ]);

        $tenant->update($validated);

        return redirect()->back()->with('success', "Subscription updated for '{$tenant->name}'.");
    }
}
