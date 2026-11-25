@extends('layouts.app')

@section('title', 'SaaS Platform Super-Admin – Tenants & Subscriptions')

@section('content')
<div class="space-y-4">
    <!-- Top Platform KPI Cards -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-slate-800">SaaS Multi-Tenant Management</h1>
            <p class="text-xs text-slate-500">Global tenant academies, subscription tiers, resource caps, and platform telemetry.</p>
        </div>
        <button onclick="document.getElementById('newTenantModal').classList.remove('hidden')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded font-medium text-xs shadow-sm flex items-center gap-1.5 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Onboard New Academy</span>
        </button>
    </div>

    <!-- Telemetry Cards -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-2.5">
        <div class="bg-white border border-slate-200 rounded p-2.5">
            <div class="text-[10px] uppercase font-bold text-slate-400">Total Academies</div>
            <div class="text-base font-bold text-slate-800 mt-0.5">{{ $metrics['total_tenants'] }}</div>
            <div class="text-[10px] text-emerald-600 font-medium">Platform wide</div>
        </div>
        <div class="bg-white border border-slate-200 rounded p-2.5">
            <div class="text-[10px] uppercase font-bold text-slate-400">Active Paid Plans</div>
            <div class="text-base font-bold text-emerald-700 mt-0.5">{{ $metrics['active_subscriptions'] }}</div>
            <div class="text-[10px] text-slate-400">Paying customers</div>
        </div>
        <div class="bg-white border border-slate-200 rounded p-2.5">
            <div class="text-[10px] uppercase font-bold text-slate-400">Trial Accounts</div>
            <div class="text-base font-bold text-amber-600 mt-0.5">{{ $metrics['trial_tenants'] }}</div>
            <div class="text-[10px] text-slate-400">14-day evaluation</div>
        </div>
        <div class="bg-white border border-slate-200 rounded p-2.5">
            <div class="text-[10px] uppercase font-bold text-slate-400">Active Students</div>
            <div class="text-base font-bold text-blue-700 mt-0.5">{{ $metrics['total_students_across_platform'] }}</div>
            <div class="text-[10px] text-slate-400">Enrolled learners</div>
        </div>
        <div class="bg-white border border-slate-200 rounded p-2.5">
            <div class="text-[10px] uppercase font-bold text-slate-400">Fleet Vehicles</div>
            <div class="text-base font-bold text-purple-700 mt-0.5">{{ $metrics['total_vehicles_managed'] }}</div>
            <div class="text-[10px] text-slate-400">Dual-control cars</div>
        </div>
        <div class="bg-white border border-slate-200 rounded p-2.5">
            <div class="text-[10px] uppercase font-bold text-slate-400">Platform Invoiced</div>
            <div class="text-base font-bold text-slate-800 mt-0.5">₹{{ number_format($metrics['total_platform_invoiced']) }}</div>
            <div class="text-[10px] text-slate-400">Aggregate Gross</div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white border border-slate-200 rounded p-2.5">
        <form method="GET" action="{{ route('superadmin.tenants') }}" class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 flex-1 max-w-md">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search academy name, email, phone..." class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none">
                <select name="status" class="text-xs px-2.5 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="trial" {{ $status === 'trial' ? 'selected' : '' }}>Trial</option>
                    <option value="expired" {{ $status === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
                <button type="submit" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium">Filter</button>
            </div>
            <span class="text-xs text-slate-500">Showing {{ $tenants->total() }} tenant academies</span>
        </form>
    </div>

    <!-- Tenants Table -->
    <div class="bg-white border border-slate-200 rounded overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-2 px-3">Academy Name / Slug</th>
                        <th class="py-2 px-3">Contact Person</th>
                        <th class="py-2 px-3">Plan Tier</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3 text-center">Branches</th>
                        <th class="py-2 px-3 text-center">Students</th>
                        <th class="py-2 px-3 text-center">Vehicles</th>
                        <th class="py-2 px-3">Valid Until</th>
                        <th class="py-2 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tenants as $tenant)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-2 px-3">
                            <div class="font-semibold text-slate-800">{{ $tenant->name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $tenant->slug }}</div>
                        </td>
                        <td class="py-2 px-3">
                            <div class="font-medium text-slate-700">{{ $tenant->contact_person }}</div>
                            <div class="text-[10px] text-slate-400">{{ $tenant->email }} | {{ $tenant->phone }}</div>
                        </td>
                        <td class="py-2 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                {{ $tenant->subscription_plan ?? 'Standard' }}
                            </span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $tenant->subscription_status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                {{ $tenant->subscription_status === 'trial' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}
                                {{ $tenant->subscription_status === 'expired' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}
                                {{ $tenant->subscription_status === 'suspended' ? 'bg-slate-100 text-slate-700 border border-slate-300' : '' }}">
                                {{ $tenant->subscription_status }}
                            </span>
                        </td>
                        <td class="py-2 px-3 text-center font-mono font-medium text-slate-700">
                            {{ $tenant->branches_count }} <span class="text-slate-400 text-[10px]">/ {{ $tenant->max_branches }}</span>
                        </td>
                        <td class="py-2 px-3 text-center font-mono font-medium text-slate-700">
                            {{ $tenant->students_count }} <span class="text-slate-400 text-[10px]">/ {{ $tenant->max_students }}</span>
                        </td>
                        <td class="py-2 px-3 text-center font-mono font-medium text-slate-700">
                            {{ $tenant->vehicles_count }} <span class="text-slate-400 text-[10px]">/ {{ $tenant->max_vehicles }}</span>
                        </td>
                        <td class="py-2 px-3 text-[11px] text-slate-600">
                            {{ $tenant->subscription_ends_at ? $tenant->subscription_ends_at->format('d M Y') : 'Lifetime' }}
                        </td>
                        <td class="py-2 px-3 text-right">
                            <button onclick="editSubscription({{ $tenant->toJson() }})" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-medium transition-colors">
                                Edit Plan
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-6 text-center text-slate-400 text-xs">No academies registered on the platform yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tenants->hasPages())
        <div class="p-2.5 border-t border-slate-200 bg-slate-50">
            {{ $tenants->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal: Onboard New Academy -->
<div id="newTenantModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-3">
    <div class="bg-white rounded-lg border border-slate-200 shadow-xl max-w-lg w-full p-4">
        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
            <h3 class="text-sm font-bold text-slate-800">Onboard New Driving School Academy</h3>
            <button onclick="document.getElementById('newTenantModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form method="POST" action="{{ route('superadmin.tenants.store') }}" class="mt-3 space-y-2.5">
            @csrf
            <div>
                <label class="block text-[11px] font-medium text-slate-700">Academy Business Name *</label>
                <input type="text" name="name" required placeholder="e.g. Taj Motor Driving School Agra" class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-medium text-slate-700">Contact Person *</label>
                    <input type="text" name="contact_person" required placeholder="e.g. Suresh Kumar" class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-700">Contact Phone *</label>
                    <input type="text" name="phone" required placeholder="e.g. +91 98370 43210" class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-medium text-slate-700">Owner Email Address *</label>
                <input type="email" name="email" required placeholder="e.g. director@thrumble.driving-school" class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-medium text-slate-700">Subscription Tier *</label>
                    <select name="subscription_plan" class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none">
                        <option value="Starter">Starter (Single Branch)</option>
                        <option value="Professional" selected>Professional (Multi-Branch)</option>
                        <option value="Enterprise">Enterprise (High Volume)</option>
                        <option value="Multi-Branch Fleet">Multi-Branch Fleet (Unlimited)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-700">Initial Status *</label>
                    <select name="subscription_status" class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none">
                        <option value="active" selected>Active Paid</option>
                        <option value="trial">14-Day Free Trial</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-[11px] font-medium text-slate-700">Max Branches</label>
                    <input type="number" name="max_branches" value="5" min="1" class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-700">Max Students</label>
                    <input type="number" name="max_students" value="500" min="10" class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-700">Max Vehicles</label>
                    <input type="number" name="max_vehicles" value="20" min="1" class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('newTenantModal').classList.add('hidden')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium">Cancel</button>
                <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium">Provision Academy</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Subscription -->
<div id="editSubscriptionModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-3">
    <div class="bg-white rounded-lg border border-slate-200 shadow-xl max-w-md w-full p-4">
        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
            <h3 class="text-sm font-bold text-slate-800" id="editModalTitle">Update Subscription Plan</h3>
            <button onclick="document.getElementById('editSubscriptionModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form id="editSubscriptionForm" method="POST" action="" class="mt-3 space-y-2.5">
            @csrf
            <div>
                <label class="block text-[11px] font-medium text-slate-700">Subscription Tier *</label>
                <select name="subscription_plan" id="edit_plan" class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none">
                    <option value="Starter">Starter</option>
                    <option value="Professional">Professional</option>
                    <option value="Enterprise">Enterprise</option>
                    <option value="Multi-Branch Fleet">Multi-Branch Fleet</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-medium text-slate-700">Account Status *</label>
                <select name="subscription_status" id="edit_status" class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none">
                    <option value="active">Active</option>
                    <option value="trial">Trial</option>
                    <option value="expired">Expired</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="block text-[11px] font-medium text-slate-700">Max Branches</label>
                    <input type="number" name="max_branches" id="edit_branches" required min="1" class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-700">Max Students</label>
                    <input type="number" name="max_students" id="edit_students" required min="10" class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-700">Max Vehicles</label>
                    <input type="number" name="max_vehicles" id="edit_vehicles" required min="1" class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded">
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('editSubscriptionModal').classList.add('hidden')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium">Cancel</button>
                <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function editSubscription(tenant) {
    document.getElementById('editModalTitle').textContent = 'Update Plan: ' + tenant.name;
    document.getElementById('editSubscriptionForm').action = '/superadmin/tenants/' + tenant.id + '/subscription';
    document.getElementById('edit_plan').value = tenant.subscription_plan || 'Professional';
    document.getElementById('edit_status').value = tenant.subscription_status || 'active';
    document.getElementById('edit_branches').value = tenant.max_branches || 5;
    document.getElementById('edit_students').value = tenant.max_students || 500;
    document.getElementById('edit_vehicles').value = tenant.max_vehicles || 20;
    document.getElementById('editSubscriptionModal').classList.remove('hidden');
}
</script>
@endsection
