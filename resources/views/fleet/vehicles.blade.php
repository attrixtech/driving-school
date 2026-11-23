@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ newVehModal: false }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Vehicle & Fleet Registry</h1>
            <p class="text-xs text-slate-500">Dual-control training cars, 2-wheelers, odometer readings, and statutory document renewals.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('fleet.fuel-maintenance') }}" class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded text-xs font-semibold shadow-xs">
                Fuel & Maintenance Logs &rarr;
            </a>
            <button @click="newVehModal = true" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Register Vehicle</span>
            </button>
        </div>
    </div>

    <!-- Vehicles Table -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3">Reg. Number</th>
                        <th class="py-2 px-3">Make & Model</th>
                        <th class="py-2 px-3">Category</th>
                        <th class="py-2 px-3">Specs</th>
                        <th class="py-2 px-3">Dual Control</th>
                        <th class="py-2 px-3">Odometer</th>
                        <th class="py-2 px-3">Document Validity & Expiry</th>
                        <th class="py-2 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($vehicles as $veh)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 bg-slate-100 text-slate-900 rounded font-mono font-bold text-[11px] border border-slate-200">
                                    {{ $veh->registration_number }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-slate-900">{{ $veh->make }} {{ $veh->model }}</div>
                                <div class="text-[10px] text-slate-400">{{ $veh->year }} &bull; {{ $veh->color }} &bull; Branch: {{ $veh->branch?->name }}</div>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $veh->category?->code ?? 'LMV' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap text-slate-600 capitalize">
                                {{ $veh->transmission }} &bull; {{ $veh->fuel_type }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                @if($veh->dual_control_fitted === 'yes')
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        &#10003; Dual Pedals Fitted
                                    </span>
                                @else
                                    <span class="text-slate-400 text-[10px]">Standard</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap font-mono font-semibold text-slate-800">
                                {{ number_format($veh->odometer_reading) }} km
                            </td>
                            <td class="py-2.5 px-3 text-[11px] space-y-0.5">
                                @if($veh->pollution_expiry_date)
                                    @php $pucSoon = $veh->pollution_expiry_date <= now()->addDays(20); @endphp
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[9px] uppercase font-bold text-slate-400">PUC:</span>
                                        <span class="{{ $pucSoon ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                            {{ $veh->pollution_expiry_date->format('d M y') }}
                                            @if($pucSoon) (Due soon!) @endif
                                        </span>
                                    </div>
                                @endif

                                @if($veh->insurance_expiry_date)
                                    @php $insSoon = $veh->insurance_expiry_date <= now()->addDays(20); @endphp
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[9px] uppercase font-bold text-slate-400">INS:</span>
                                        <span class="{{ $insSoon ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                            {{ $veh->insurance_expiry_date->format('d M y') }}
                                            @if($insSoon) (Due soon!) @endif
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase {{ $veh->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                    {{ $veh->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-xs text-slate-400 italic">No vehicles registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vehicles->hasPages())
            <div class="px-3 py-2 border-t border-slate-200 bg-slate-50">
                {{ $vehicles->links() }}
            </div>
        @endif
    </div>

    <!-- REGISTER VEHICLE MODAL -->
    <div x-show="newVehModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="newVehModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Register New Training Vehicle</h3>
                <button @click="newVehModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('vehicles.store') }}" class="p-4 space-y-3 text-xs">
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
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Vehicle Category *</label>
                        <select name="vehicle_category_id" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Reg Number *</label>
                        <input type="text" name="registration_number" required placeholder="KA-01-AB-1234" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Make *</label>
                        <input type="text" name="make" required placeholder="Maruti Suzuki" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Model *</label>
                        <input type="text" name="model" required placeholder="Swift VXi" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Transmission *</label>
                        <select name="transmission" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="manual">Manual</option>
                            <option value="automatic">Automatic</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Fuel Type *</label>
                        <select name="fuel_type" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="petrol">Petrol</option>
                            <option value="diesel">Diesel</option>
                            <option value="cng">CNG</option>
                            <option value="electric">Electric (EV)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Dual Control Fitted *</label>
                        <select name="dual_control_fitted" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="yes">Yes (Dual Pedals)</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Current Odometer (km) *</label>
                        <input type="number" name="odometer_reading" value="0" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Manufacturing Year</label>
                        <input type="number" name="year" value="2023" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-1 border-t border-slate-100">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Insurance Expiry Date</label>
                        <input type="date" name="insurance_expiry_date" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Pollution (PUC) Expiry Date</label>
                        <input type="date" name="pollution_expiry_date" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="newVehModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold">Save Vehicle</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
