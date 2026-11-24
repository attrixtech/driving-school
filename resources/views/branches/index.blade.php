@extends('layouts.app')

@section('title', 'Branch Network & Management')
@section('page-title', 'Branch Network')

@section('content')
<div class="space-y-4" x-data="{ 
    createModal: {{ request()->has('new') ? 'true' : 'false' }}, 
    editModal: false,
    currentBranch: {},
    editBranch(b) {
        this.currentBranch = Object.assign({}, b);
        this.editModal = true;
    }
}">

    <!-- TOP HEADER BAR -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 rounded-lg border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Driving School Branch Network
            </h1>
            <p class="text-xs text-slate-500">Configure driving school branches, headquarters location, local phone & address, and branch fleet distribution.</p>
        </div>

        <div class="flex items-center gap-2">
            <button @click="createModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>+ Add New Branch</span>
            </button>
            <a href="{{ route('settings.company') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded text-xs font-semibold transition-colors">
                Company Profile
            </a>
        </div>
    </div>

    <!-- TELEMETRY KPI METRICS BAR -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
        <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Total Branches</div>
                <div class="text-base font-bold text-slate-800">{{ $totalBranches }} <span class="text-[11px] font-normal text-slate-500">locations</span></div>
            </div>
        </div>

        <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Active Locations</div>
                <div class="text-base font-bold text-emerald-700">{{ $activeBranches }} <span class="text-[11px] font-normal text-slate-500">operational</span></div>
            </div>
        </div>

        <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Instructors</div>
                <div class="text-base font-bold text-indigo-700">{{ $totalInstructors }} <span class="text-[11px] font-normal text-slate-500">trainers</span></div>
            </div>
        </div>

        <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Total Fleet</div>
                <div class="text-base font-bold text-purple-700">{{ $totalVehicles }} <span class="text-[11px] font-normal text-slate-500">vehicles</span></div>
            </div>
        </div>
    </div>

    <!-- SEARCH & FILTER BAR -->
    <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('branches.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search branch name, code, city..." 
                           class="w-full pl-7 pr-3 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <select name="status" class="px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 bg-white">
                    <option value="">All Statuses</option>
                    <option value="1" {{ $statusFilter === '1' ? 'selected' : '' }}>Active Only</option>
                    <option value="0" {{ $statusFilter === '0' ? 'selected' : '' }}>Inactive Only</option>
                </select>

                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded text-xs font-semibold">
                    Filter
                </button>

                @if($search || $statusFilter !== null)
                    <a href="{{ route('branches.index') }}" class="px-2 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded text-xs border border-slate-200">
                        ✕
                    </a>
                @endif
            </div>

            <div class="text-[11px] text-slate-500">
                Operating in multi-branch mode across all student admissions, lesson scheduling, and financial ledgers.
            </div>
        </form>
    </div>

    <!-- BRANCHES DIRECTORY LIST -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
        @forelse($branches as $b)
            @php
                $isCurrentActive = ($activeBranchId == $b->id);
            @endphp
            <div class="bg-white rounded-lg border {{ $b->is_main_branch ? 'border-blue-300 ring-1 ring-blue-200' : 'border-slate-200' }} shadow-xs p-4 flex flex-col justify-between hover:shadow-md transition-shadow">
                <div>
                    <!-- Branch Top Card Header -->
                    <div class="flex items-start justify-between gap-2 pb-2.5 border-b border-slate-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-900">{{ $b->name }}</h3>
                                @if($b->is_main_branch)
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-blue-100 text-blue-800 border border-blue-200 uppercase">
                                        Headquarters
                                    </span>
                                @endif
                                @if($isCurrentActive)
                                    <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase">
                                        Active Session
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] font-mono text-slate-500 mt-0.5">
                                Code: <span class="font-bold text-slate-700">{{ $b->code }}</span>
                            </div>
                        </div>

                        <!-- Status badge -->
                        <div>
                            @if($b->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactive
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Contact & Address -->
                    <div class="py-2.5 space-y-1.5 text-xs text-slate-600">
                        <div class="flex items-start gap-1.5">
                            <span class="text-slate-400 shrink-0">📍</span>
                            <span>{{ $b->address ? $b->address . ', ' : '' }}{{ $b->city }}, {{ $b->state }} {{ $b->postal_code }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-[11px]">
                            @if($b->phone)
                                <div class="flex items-center gap-1">
                                    <span class="text-slate-400">📞</span>
                                    <span>{{ $b->phone }}</span>
                                </div>
                            @endif
                            @if($b->email)
                                <div class="flex items-center gap-1">
                                    <span class="text-slate-400">✉️</span>
                                    <span>{{ $b->email }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Branch Counts / Telemetry -->
                    <div class="grid grid-cols-4 gap-2 pt-2 border-t border-slate-100 text-center text-xs">
                        <div class="p-1.5 bg-slate-50 rounded border border-slate-100">
                            <div class="text-[10px] text-slate-400 font-semibold uppercase">Students</div>
                            <div class="text-xs font-bold text-slate-800">{{ $b->students_count }}</div>
                        </div>
                        <div class="p-1.5 bg-slate-50 rounded border border-slate-100">
                            <div class="text-[10px] text-slate-400 font-semibold uppercase">Trainers</div>
                            <div class="text-xs font-bold text-slate-800">{{ $b->instructors_count }}</div>
                        </div>
                        <div class="p-1.5 bg-slate-50 rounded border border-slate-100">
                            <div class="text-[10px] text-slate-400 font-semibold uppercase">Vehicles</div>
                            <div class="text-xs font-bold text-slate-800">{{ $b->vehicles_count }}</div>
                        </div>
                        <div class="p-1.5 bg-slate-50 rounded border border-slate-100">
                            <div class="text-[10px] text-slate-400 font-semibold uppercase">Admissions</div>
                            <div class="text-xs font-bold text-slate-800">{{ $b->admissions_count }}</div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions Bar -->
                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
                    <!-- Switch operating branch -->
                    <div>
                        @if(!$isCurrentActive && $b->is_active)
                            <form method="POST" action="{{ route('branch.switch', $b->id) }}">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold rounded text-[11px] transition-colors flex items-center gap-1">
                                    <span>Switch Session Here</span> &rarr;
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="flex items-center gap-1.5">
                        <!-- Set as Main Branch -->
                        @if(!$b->is_main_branch && $b->is_active)
                            <form method="POST" action="{{ route('branches.set-main', $b->id) }}" onsubmit="return confirm('Designate {{ $b->name }} as the primary headquarters branch?');">
                                @csrf
                                <button type="submit" class="p-1 text-slate-500 hover:text-amber-600 hover:bg-slate-100 rounded text-[11px] font-medium" title="Make Primary Headquarters">
                                    Set as HQ
                                </button>
                            </form>
                        @endif

                        <!-- Toggle Active Status -->
                        @if(!$b->is_main_branch)
                            <form method="POST" action="{{ route('branches.toggle', $b->id) }}" onsubmit="return confirm('Change operational status for {{ $b->name }}?');">
                                @csrf
                                <button type="submit" class="p-1 {{ $b->is_active ? 'text-slate-400 hover:text-rose-600' : 'text-slate-400 hover:text-emerald-600' }} hover:bg-slate-100 rounded transition-colors" title="{{ $b->is_active ? 'Deactivate Branch' : 'Activate Branch' }}">
                                    @if($b->is_active)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    @endif
                                </button>
                            </form>
                        @endif

                        <!-- Edit Branch Button -->
                        <button @click="editBranch({
                            id: {{ $b->id }},
                            name: '{{ addslashes($b->name) }}',
                            code: '{{ addslashes($b->code) }}',
                            phone: '{{ addslashes($b->phone ?? '') }}',
                            email: '{{ addslashes($b->email ?? '') }}',
                            address: '{{ addslashes($b->address ?? '') }}',
                            city: '{{ addslashes($b->city) }}',
                            state: '{{ addslashes($b->state) }}',
                            postal_code: '{{ addslashes($b->postal_code ?? '') }}',
                            is_main_branch: {{ $b->is_main_branch ? 1 : 0 }},
                            is_active: {{ $b->is_active ? 1 : 0 }}
                        })" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded text-[11px] transition-colors flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            <span>Edit Branch</span>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center text-slate-400 bg-white rounded-lg border border-slate-200">
                No branches found matching your search. Click <strong>+ Add New Branch</strong> to create one.
            </div>
        @endforelse
    </div>

    <!-- MODAL 1: ADD NEW BRANCH -->
    <div x-show="createModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div @click="createModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative inline-block w-full max-w-lg p-5 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">+</span>
                            Add New Operating Branch
                        </h3>
                        <p class="text-[11px] text-slate-500">Provide physical location, branch identification code, and contact information.</p>
                    </div>
                    <button @click="createModal = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                </div>

                <form method="POST" action="{{ route('branches.store') }}" class="mt-4 space-y-3.5 text-xs">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Branch Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required placeholder="e.g. Jayanagar Training Branch"
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Branch Code <span class="text-rose-500">*</span></label>
                            <input type="text" name="code" required placeholder="e.g. JAYA-03"
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 font-mono uppercase">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Contact Phone</label>
                            <input type="tel" name="phone" placeholder="e.g. +91 80 2665 4321"
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Official Branch Email</label>
                            <input type="email" name="email" placeholder="e.g. jayanagar@apexdriving.com"
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Premises Address</label>
                        <input type="text" name="address" placeholder="e.g. 45th Cross, 8th Block, Near Metro Station"
                               class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-3 gap-2.5">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">City <span class="text-rose-500">*</span></label>
                            <input type="text" name="city" value="Bengaluru" required
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">State <span class="text-rose-500">*</span></label>
                            <input type="text" name="state" value="Karnataka" required
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Postal PIN Code</label>
                            <input type="text" name="postal_code" placeholder="560041"
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-200 space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_main_branch" value="1" class="rounded text-blue-600 focus:ring-0">
                            <span class="font-semibold text-slate-800">Set as primary Headquarters location</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded text-blue-600 focus:ring-0">
                            <span class="font-medium text-slate-700">Branch is active and open for student admissions</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                        <button type="button" @click="createModal = false" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                            Create Branch
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 2: EDIT BRANCH -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div @click="editModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative inline-block w-full max-w-lg p-5 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Edit Branch: <span class="text-blue-600" x-text="currentBranch.name"></span></h3>
                        <p class="text-[11px] text-slate-500">Update branch premises address, contact numbers, or operational status.</p>
                    </div>
                    <button @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                </div>

                <form method="POST" :action="'{{ url('/branches') }}/' + currentBranch.id" class="mt-4 space-y-3.5 text-xs">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Branch Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="currentBranch.name" required
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Branch Code <span class="text-rose-500">*</span></label>
                            <input type="text" name="code" x-model="currentBranch.code" required
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 font-mono uppercase">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Contact Phone</label>
                            <input type="tel" name="phone" x-model="currentBranch.phone"
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Official Branch Email</label>
                            <input type="email" name="email" x-model="currentBranch.email"
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Premises Address</label>
                        <input type="text" name="address" x-model="currentBranch.address"
                               class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-3 gap-2.5">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">City <span class="text-rose-500">*</span></label>
                            <input type="text" name="city" x-model="currentBranch.city" required
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">State <span class="text-rose-500">*</span></label>
                            <input type="text" name="state" x-model="currentBranch.state" required
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Postal PIN Code</label>
                            <input type="text" name="postal_code" x-model="currentBranch.postal_code"
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="pt-2 border-t border-slate-200 space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_main_branch" value="1" :checked="currentBranch.is_main_branch == 1" class="rounded text-blue-600 focus:ring-0">
                            <span class="font-semibold text-slate-800">Set as primary Headquarters location</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" :checked="currentBranch.is_active == 1" class="rounded text-blue-600 focus:ring-0">
                            <span class="font-medium text-slate-700">Branch is active and open for student admissions</span>
                        </label>
                    </div>

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

</div>
@endsection
