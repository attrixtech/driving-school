@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ newInstModal: false }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Instructor Management</h1>
            <p class="text-xs text-slate-500">Manage certified driving trainers, license validity, working hours, and performance ratings.</p>
        </div>
        <button @click="newInstModal = true" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Add Instructor</span>
        </button>
    </div>

    <!-- Instructor Table -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3">Code</th>
                        <th class="py-2 px-3">Instructor Name</th>
                        <th class="py-2 px-3">Contact</th>
                        <th class="py-2 px-3">Branch</th>
                        <th class="py-2 px-3">License & Expiry</th>
                        <th class="py-2 px-3">Vehicle Classes</th>
                        <th class="py-2 px-3">Completed Sessions</th>
                        <th class="py-2 px-3">Rating</th>
                        <th class="py-2 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($instructors as $inst)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3 font-mono font-semibold text-slate-800">
                                {{ $inst->instructor_code }}
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-slate-900">{{ $inst->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $inst->experience_years }} years experience</div>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $inst->phone }}</div>
                                <div class="text-[10px] text-slate-400">{{ $inst->email ?? 'No email' }}</div>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600">
                                {{ $inst->branch?->name }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <div class="font-mono text-[11px] text-slate-800">{{ $inst->license_number }}</div>
                                @if($inst->license_expiry_date)
                                    @php $isExpSoon = $inst->license_expiry_date <= now()->addDays(30); @endphp
                                    <div class="text-[10px] {{ $isExpSoon ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                                        Expires: {{ $inst->license_expiry_date->format('d M Y') }}
                                        @if($isExpSoon) (Expiring soon!) @endif
                                    </div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="flex items-center gap-1 flex-wrap">
                                    @foreach($inst->vehicle_types ?? [] as $vt)
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $vt }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-2.5 px-3 font-bold text-slate-800">
                                {{ $inst->lesson_schedules_count }} sessions
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <div class="flex items-center gap-1 font-bold text-amber-600">
                                    <span>&#9733;</span>
                                    <span>{{ number_format($inst->rating, 1) }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase {{ $inst->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $inst->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-6 text-center text-xs text-slate-400 italic">No instructors registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($instructors->hasPages())
            <div class="px-3 py-2 border-t border-slate-200 bg-slate-50">
                {{ $instructors->links() }}
            </div>
        @endif
    </div>

    <!-- NEW INSTRUCTOR MODAL -->
    <div x-show="newInstModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="newInstModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Add Certified Driving Instructor</h3>
                <button @click="newInstModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('instructors.store') }}" class="p-4 space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Branch *</label>
                        <select name="branch_id" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Full Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Anand Gowda" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Phone Number *</label>
                        <input type="text" name="phone" required placeholder="+91 98370 00000" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="email" placeholder="anand@thrumble.driving-school" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Driving License Number *</label>
                        <input type="text" name="license_number" required placeholder="UP-80-2015-0012345" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">License Expiry Date</label>
                        <input type="date" name="license_expiry_date" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Experience (Years) *</label>
                        <input type="number" name="experience_years" value="5" min="0" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Monthly Salary (₹)</label>
                        <input type="number" step="0.01" name="monthly_salary" placeholder="25000.00" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Eligible Vehicle Classes *</label>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-1.5">
                            <input type="checkbox" name="vehicle_types[]" value="LMV" checked class="rounded border-slate-300 text-blue-600">
                            <span>LMV (Car)</span>
                        </label>
                        <label class="flex items-center gap-1.5">
                            <input type="checkbox" name="vehicle_types[]" value="LMV-A" checked class="rounded border-slate-300 text-blue-600">
                            <span>LMV-A (Automatic)</span>
                        </label>
                        <label class="flex items-center gap-1.5">
                            <input type="checkbox" name="vehicle_types[]" value="MCWG" checked class="rounded border-slate-300 text-blue-600">
                            <span>MCWG (Bike)</span>
                        </label>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="newInstModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold">Save Instructor</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
