@extends('layouts.app')

@section('title', 'Staff & User Management')
@section('page-title', 'Staff & User Directory')

@section('content')
<div class="space-y-4" x-data="{ 
    createModal: {{ request()->has('new') ? 'true' : 'false' }}, 
    editModal: false, 
    passwordModal: false,
    currentUser: {},
    editUser(u) {
        this.currentUser = Object.assign({}, u);
        this.editModal = true;
    },
    openPassword(u) {
        this.currentUser = Object.assign({}, u);
        this.passwordModal = true;
    }
}">

    <!-- TOP HEADER BAR -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 rounded-lg border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                Staff & User Management
            </h1>
            <p class="text-xs text-slate-500">Create, configure access roles, and assign operational branches for all driving school team members.</p>
        </div>

        <div class="flex items-center gap-2">
            <button @click="createModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>+ Add New User</span>
            </button>
        </div>
    </div>

    <!-- TELEMETRY KPI METRICS -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-9 h-9 rounded bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Users</div>
                <div class="text-lg font-bold text-slate-800">{{ $totalUsers }}</div>
            </div>
        </div>

        <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-9 h-9 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Active Staff</div>
                <div class="text-lg font-bold text-emerald-700">{{ $activeUsers }}</div>
            </div>
        </div>

        <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-9 h-9 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Instructors</div>
                <div class="text-lg font-bold text-indigo-700">{{ $instructorCount }}</div>
            </div>
        </div>

        <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-9 h-9 rounded bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <div>
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Admins & Managers</div>
                <div class="text-lg font-bold text-purple-700">{{ $adminCount }}</div>
            </div>
        </div>
    </div>

    <!-- FILTER & SEARCH BAR -->
    <div class="bg-white p-3 rounded-lg border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-2.5">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Search Staff / User</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, email, phone..." 
                           class="w-full pl-8 pr-3 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Role filter -->
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Filter by Role</label>
                <select name="role" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                    <option value="">All Roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->slug }}" {{ $roleFilter == $r->slug ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Branch filter -->
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Filter by Branch</label>
                <select name="branch" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchFilter == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action buttons -->
            <div class="flex items-end gap-1.5">
                <button type="submit" class="w-full py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded text-xs font-medium transition-colors">
                    Filter
                </button>
                @if($search || $roleFilter || $branchFilter || $statusFilter !== null)
                    <a href="{{ route('users.index') }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded text-xs font-medium border border-slate-200" title="Reset Filters">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- USERS DATA TABLE -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase text-[10px] tracking-wider">
                        <th class="py-2.5 px-3">User & Contact</th>
                        <th class="py-2.5 px-3">Role</th>
                        <th class="py-2.5 px-3">Primary Branch</th>
                        <th class="py-2.5 px-3">Assigned Branches</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3">Joined Date</th>
                        <th class="py-2.5 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <!-- User Details -->
                            <td class="py-2.5 px-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center font-bold text-slate-700 text-xs shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                                            <span>{{ $user->name }}</span>
                                            @if($user->id === auth()->id())
                                                <span class="text-[9px] bg-blue-100 text-blue-700 px-1 py-0.2 rounded font-bold">You</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-2">
                                            <span>{{ $user->email }}</span>
                                            @if($user->phone)
                                                <span>•</span>
                                                <span>{{ $user->phone }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Role Badge -->
                            <td class="py-2.5 px-3">
                                @php
                                    $roleColors = [
                                        'business_owner' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'school_admin' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'super_admin' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'branch_manager' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'instructor' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'admission_executive' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'receptionist' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'accountant' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                        'student' => 'bg-sky-50 text-sky-700 border-sky-200',
                                    ];
                                    $colorClass = $roleColors[$user->role_slug] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border {{ $colorClass }}">
                                    {{ ucwords(str_replace('_', ' ', $user->role_slug)) }}
                                </span>
                            </td>

                            <!-- Primary Branch -->
                            <td class="py-2.5 px-3">
                                @if($user->branch)
                                    <span class="font-medium text-slate-700">{{ $user->branch->name }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $user->branch->code }}</span>
                                @else
                                    <span class="text-slate-400 italic">All Branches (Headquarters)</span>
                                @endif
                            </td>

                            <!-- Multi-branch access -->
                            <td class="py-2.5 px-3">
                                <div class="flex flex-wrap gap-1 max-w-[200px]">
                                    @forelse($user->branches as $b)
                                        <span class="inline-block text-[10px] bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded border border-slate-200" title="{{ $b->name }}">
                                            {{ $b->code ?? substr($b->name, 0, 10) }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 text-[11px]">—</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="py-2.5 px-3 text-center">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactive
                                    </span>
                                @endif
                            </td>

                            <!-- Joined Date -->
                            <td class="py-2.5 px-3 text-slate-500 text-[11px]">
                                {{ $user->created_at ? $user->created_at->format('M d, Y') : '—' }}
                            </td>

                            <!-- Actions -->
                            <td class="py-2.5 px-3 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Edit Trigger -->
                                    <button @click="editUser({
                                        id: {{ $user->id }},
                                        name: '{{ addslashes($user->name) }}',
                                        email: '{{ addslashes($user->email) }}',
                                        phone: '{{ addslashes($user->phone ?? '') }}',
                                        role_slug: '{{ $user->role_slug }}',
                                        branch_id: '{{ $user->branch_id }}',
                                        branch_ids: {{ json_encode($user->branches->pluck('id')) }},
                                        is_active: {{ $user->is_active ? 1 : 0 }}
                                    })" class="p-1 text-slate-500 hover:text-blue-600 hover:bg-slate-100 rounded transition-colors" title="Edit User">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>

                                    <!-- Password Reset Trigger -->
                                    <button @click="openPassword({
                                        id: {{ $user->id }},
                                        name: '{{ addslashes($user->name) }}',
                                        email: '{{ addslashes($user->email) }}'
                                    })" class="p-1 text-slate-500 hover:text-amber-600 hover:bg-slate-100 rounded transition-colors" title="Reset Password">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                    </button>

                                    <!-- Toggle Active Status Form -->
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.toggle', $user->id) }}" class="inline" onsubmit="return confirm('Change status for {{ $user->name }}?');">
                                            @csrf
                                            <button type="submit" class="p-1 {{ $user->is_active ? 'text-slate-400 hover:text-rose-600' : 'text-slate-400 hover:text-emerald-600' }} hover:bg-slate-100 rounded transition-colors" title="{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}">
                                                @if($user->is_active)
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                @endif
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <p class="font-medium text-slate-600">No staff users found matching query</p>
                                    <button @click="createModal = true" class="mt-2 text-blue-600 hover:underline font-semibold text-xs">
                                        + Add New Staff Member
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-3 border-t border-slate-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL 1: ADD NEW USER -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div @click="createModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative inline-block w-full max-w-lg p-5 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">+</span>
                            Create New Staff / User Account
                        </h3>
                        <p class="text-[11px] text-slate-500">Provide staff profile details, login credentials, and branch permissions.</p>
                    </div>
                    <button @click="createModal = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                </div>

                <form method="POST" action="{{ route('users.store') }}" class="mt-4 space-y-3.5 text-xs">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="e.g. Ramesh Kumar"
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Email Address <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" required placeholder="e.g. ramesh@apexdriving.com"
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Phone Number</label>
                            <input type="tel" name="phone" placeholder="e.g. +91 98765 43210"
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Access Role <span class="text-rose-500">*</span></label>
                            <select name="role_slug" required class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 bg-white">
                                <option value="">-- Choose Role --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->slug }}">
                                        {{ $r->name }} ({{ ucwords(str_replace('_', ' ', $r->slug)) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Primary Base Branch</label>
                            <select name="branch_id" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 bg-white">
                                <option value="">None (Headquarters / All Branches)</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Initial Password <span class="text-rose-500">*</span></label>
                            <input type="password" name="password" required minlength="6" placeholder="Min. 6 characters"
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Multi-branch access checkboxes -->
                    <div class="bg-slate-50 p-2.5 rounded border border-slate-200">
                        <label class="block font-semibold text-slate-700 mb-1.5 text-[11px]">Multi-Branch Access Permissions</label>
                        <p class="text-[10px] text-slate-500 mb-2">Check all branches this user is allowed to switch between and operate in.</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($branches as $b)
                                <label class="flex items-center gap-2 cursor-pointer text-[11px] text-slate-700">
                                    <input type="checkbox" name="branches[]" value="{{ $b->id }}" class="rounded text-blue-600 focus:ring-0 border-slate-300">
                                    <span>{{ $b->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Active Toggle -->
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="create_active" value="1" checked class="rounded text-blue-600 focus:ring-0 border-slate-300">
                        <label for="create_active" class="font-medium text-slate-700 cursor-pointer">Activate account immediately upon creation</label>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                        <button type="button" @click="createModal = false" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                            Create Staff User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 2: EDIT USER -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div @click="editModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative inline-block w-full max-w-lg p-5 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Edit User Profile: <span class="text-blue-600" x-text="currentUser.name"></span></h3>
                        <p class="text-[11px] text-slate-500">Update role assignments, contact details, or assigned branches.</p>
                    </div>
                    <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                </div>

                <form method="POST" :action="'{{ url('/users') }}/' + currentUser.id" class="mt-4 space-y-3.5 text-xs">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="currentUser.name" required
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Email Address <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" x-model="currentUser.email" required
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Phone Number</label>
                            <input type="tel" name="phone" x-model="currentUser.phone"
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Access Role <span class="text-rose-500">*</span></label>
                            <select name="role_slug" x-model="currentUser.role_slug" required class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 bg-white">
                                @foreach($roles as $r)
                                    <option value="{{ $r->slug }}">{{ $r->name }} ({{ ucwords(str_replace('_', ' ', $r->slug)) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Primary Base Branch</label>
                        <select name="branch_id" x-model="currentUser.branch_id" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 bg-white">
                            <option value="">None (Headquarters / All Branches)</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Multi-branch access checkboxes -->
                    <div class="bg-slate-50 p-2.5 rounded border border-slate-200">
                        <label class="block font-semibold text-slate-700 mb-1 text-[11px]">Assigned Operating Branches</label>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            @foreach($branches as $b)
                                <label class="flex items-center gap-2 cursor-pointer text-[11px] text-slate-700">
                                    <input type="checkbox" name="branches[]" value="{{ $b->id }}" 
                                           :checked="currentUser.branch_ids && currentUser.branch_ids.includes({{ $b->id }})"
                                           class="rounded text-blue-600 focus:ring-0 border-slate-300">
                                    <span>{{ $b->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Active Toggle -->
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="edit_active" value="1" :checked="currentUser.is_active == 1" class="rounded text-blue-600 focus:ring-0 border-slate-300">
                        <label for="edit_active" class="font-medium text-slate-700 cursor-pointer">Account is active and permitted to sign in</label>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                        <button type="button" @click="editModal = false" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 3: RESET PASSWORD -->
    <div x-show="passwordModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div @click="passwordModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative inline-block w-full max-w-sm p-5 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Reset User Password</h3>
                        <p class="text-[11px] text-slate-500">User: <span class="font-semibold text-slate-800" x-text="currentUser.name"></span></p>
                    </div>
                    <button @click="passwordModal = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                </div>

                <form method="POST" :action="'{{ url('/users') }}/' + currentUser.id + '/password'" class="mt-4 space-y-3 text-xs">
                    @csrf
                    
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">New Password <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" required minlength="6" placeholder="Min. 6 characters"
                               class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Confirm New Password <span class="text-rose-500">*</span></label>
                        <input type="password" name="password_confirmation" required minlength="6" placeholder="Re-type new password"
                               class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                        <button type="button" @click="passwordModal = false" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded text-xs font-semibold shadow-xs">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
