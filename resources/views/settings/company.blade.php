@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-4" x-data="{ newBranchModal: false }">
    
    <!-- Header -->
    <div class="flex items-center justify-between pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Company & Multi-Branch Settings</h1>
            <p class="text-xs text-slate-500">Configure driving school business identity, GST registration, and branch network.</p>
        </div>
        <button @click="newBranchModal = true" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
            + Add New Branch
        </button>
    </div>

    <!-- Company Profile Form -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4 space-y-3 text-xs">
        <h2 class="text-xs font-bold text-slate-800 pb-2 border-b border-slate-100 uppercase tracking-wider text-[11px]">
            Business Legal Profile & Tax Registration
        </h2>

        <form method="POST" action="{{ route('settings.company.update') }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Legal Company Name *</label>
                    <input type="text" name="legal_name" value="{{ $company?->legal_name }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Trade / Brand Name *</label>
                    <input type="text" name="trade_name" value="{{ $company?->trade_name }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">GSTIN Number</label>
                    <input type="text" name="gstin" value="{{ $company?->gstin }}" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs font-mono">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Contact Phone *</label>
                    <input type="text" name="phone" value="{{ $company?->phone }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Official Email *</label>
                    <input type="email" name="email" value="{{ $company?->email }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Registered Address *</label>
                    <input type="text" name="address" value="{{ $company?->address }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">City *</label>
                    <input type="text" name="city" value="{{ $company?->city }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">State *</label>
                    <input type="text" name="state" value="{{ $company?->state ?? 'Karnataka' }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Postal Code *</label>
                    <input type="text" name="postal_code" value="{{ $company?->postal_code }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                    Save Company Details
                </button>
            </div>
        </form>
    </div>

    <!-- Operating Branches List -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4 space-y-3 text-xs">
        <h2 class="text-xs font-bold text-slate-800 pb-2 border-b border-slate-100 uppercase tracking-wider text-[11px]">
            Operating Branches (Multi-Branch Tenancy)
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($branches as $b)
                <div class="p-3 border rounded-md {{ $b->is_main_branch ? 'bg-blue-50/40 border-blue-200' : 'bg-slate-50 border-slate-200' }} flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900">{{ $b->name }}</span>
                            @if($b->is_main_branch)
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-blue-100 text-blue-800 uppercase">Headquarters</span>
                            @endif
                        </div>
                        <div class="text-[10px] font-mono text-slate-500 mt-0.5">Code: {{ $b->code }}</div>
                        <div class="text-slate-600 mt-2 text-[11px]">{{ $b->address }}, {{ $b->city }}</div>
                        <div class="text-slate-500 mt-1 text-[10px]">Phone: {{ $b->phone }} | Email: {{ $b->email }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- NEW BRANCH MODAL -->
    <div x-show="newBranchModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="newBranchModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Add Driving School Branch</h3>
                <button @click="newBranchModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('settings.branches.store') }}" class="p-4 space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Branch Name *</label>
                        <input type="text" name="name" required placeholder="Jayanagar Branch" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Branch Code *</label>
                        <input type="text" name="code" required placeholder="JAYA-03" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Phone</label>
                        <input type="text" name="phone" placeholder="+91 80 0000 0000" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" placeholder="branch@example.com" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Address</label>
                    <input type="text" name="address" placeholder="Premises address..." class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">City *</label>
                        <input type="text" name="city" value="Bengaluru" required class="w-full form-input border border-slate-300 rounded px-2 py-1 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">State *</label>
                        <input type="text" name="state" value="Karnataka" required class="w-full form-input border border-slate-300 rounded px-2 py-1 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Postal Code</label>
                        <input type="text" name="postal_code" placeholder="560041" class="w-full form-input border border-slate-300 rounded px-2 py-1 text-xs">
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="newBranchModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold">Create Branch</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
