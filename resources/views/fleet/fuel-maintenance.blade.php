@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ tab: 'fuel', fuelModal: false, maintModal: false }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Fleet Fuel & Maintenance Logs</h1>
            <p class="text-xs text-slate-500">Track fuel consumption, periodic maintenance, and fleet running expenses.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="fuelModal = true" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                + Log Fuel Refill
            </button>
            <button @click="maintModal = true" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded text-xs font-semibold shadow-xs">
                + Log Maintenance
            </button>
        </div>
    </div>

    <!-- Tab switcher -->
    <div class="flex items-center gap-1 border-b border-slate-200 text-xs font-medium">
        <button @click="tab = 'fuel'" :class="tab === 'fuel' ? 'border-b-2 border-blue-600 text-blue-700 font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-2">
            Fuel Refill Logs ({{ $fuelLogs->count() }})
        </button>
        <button @click="tab = 'maint'" :class="tab === 'maint' ? 'border-b-2 border-blue-600 text-blue-700 font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-2">
            Vehicle Servicing & Repairs ({{ $maintenances->count() }})
        </button>
    </div>

    <!-- FUEL LOGS TABLE -->
    <div x-show="tab === 'fuel'" class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3">Date</th>
                        <th class="py-2 px-3">Vehicle</th>
                        <th class="py-2 px-3">Quantity</th>
                        <th class="py-2 px-3">Rate / Liter</th>
                        <th class="py-2 px-3">Total Cost</th>
                        <th class="py-2 px-3">Odometer</th>
                        <th class="py-2 px-3">Fuel Station</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($fuelLogs as $fl)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2 px-3 font-semibold text-slate-800 whitespace-nowrap">{{ $fl->fuel_date->format('d M Y') }}</td>
                            <td class="py-2 px-3 whitespace-nowrap">
                                <span class="font-mono font-bold text-slate-900">{{ $fl->vehicle?->registration_number }}</span>
                                <span class="text-[10px] text-slate-400">({{ $fl->vehicle?->model }})</span>
                            </td>
                            <td class="py-2 px-3 font-medium">{{ $fl->liters }} Liters</td>
                            <td class="py-2 px-3 text-slate-600">₹{{ number_format($fl->cost_per_liter, 2) }}</td>
                            <td class="py-2 px-3 font-bold text-emerald-700">₹{{ number_format($fl->total_cost, 2) }}</td>
                            <td class="py-2 px-3 font-mono">{{ number_format($fl->odometer_reading) }} km</td>
                            <td class="py-2 px-3 text-slate-500">{{ $fl->station_name ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-xs text-slate-400 italic">No fuel logs recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MAINTENANCE TABLE -->
    <div x-show="tab === 'maint'" class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3">Service Date</th>
                        <th class="py-2 px-3">Vehicle</th>
                        <th class="py-2 px-3">Service Type</th>
                        <th class="py-2 px-3">Workshop</th>
                        <th class="py-2 px-3">Invoice No</th>
                        <th class="py-2 px-3">Cost</th>
                        <th class="py-2 px-3">Next Due</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($maintenances as $m)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2 px-3 font-semibold text-slate-800 whitespace-nowrap">{{ $m->service_date->format('d M Y') }}</td>
                            <td class="py-2 px-3 whitespace-nowrap font-mono font-bold text-slate-900">{{ $m->vehicle?->registration_number }}</td>
                            <td class="py-2 px-3 capitalize font-medium">{{ str_replace('_', ' ', $m->service_type) }}</td>
                            <td class="py-2 px-3 text-slate-600">{{ $m->workshop_name ?? 'Authorized Center' }}</td>
                            <td class="py-2 px-3 font-mono text-[11px]">{{ $m->invoice_number ?? '-' }}</td>
                            <td class="py-2 px-3 font-bold text-slate-900">₹{{ number_format($m->cost, 2) }}</td>
                            <td class="py-2 px-3 text-slate-500">
                                {{ $m->next_service_due_date ? $m->next_service_due_date->format('d M Y') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-xs text-slate-400 italic">No maintenance records logged yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- LOG FUEL MODAL -->
    <div x-show="fuelModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="fuelModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Log Vehicle Fuel Refill</h3>
                <button @click="fuelModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('fleet.fuel.store') }}" class="p-4 space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Select Vehicle *</label>
                    <select name="vehicle_id" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->registration_number }} - {{ $v->make }} {{ $v->model }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Fuel Date *</label>
                        <input type="date" name="fuel_date" value="{{ now()->toDateString() }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Odometer (km) *</label>
                        <input type="number" name="odometer_reading" required placeholder="Current km" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Quantity (Liters) *</label>
                        <input type="number" step="0.01" name="liters" required placeholder="25.00" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Cost Per Liter (₹) *</label>
                        <input type="number" step="0.01" name="cost_per_liter" value="102.80" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Fuel Station</label>
                        <input type="text" name="station_name" placeholder="Indian Oil Sanjay Place, Agra" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Receipt / Bill No</label>
                        <input type="text" name="receipt_number" placeholder="IOCL-1234" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="fuelModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold">Record Refill</button>
                </div>
            </form>
        </div>
    </div>

    <!-- LOG MAINTENANCE MODAL -->
    <div x-show="maintModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="maintModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Record Vehicle Maintenance / Repair</h3>
                <button @click="maintModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('fleet.maintenance.store') }}" class="p-4 space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Select Vehicle *</label>
                    <select name="vehicle_id" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        @foreach($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->registration_number }} - {{ $v->make }} {{ $v->model }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Service Type *</label>
                        <select name="service_type" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="routine_service">Routine Periodic Service</option>
                            <option value="brake_overhaul">Brake Overhaul / Pad Change</option>
                            <option value="clutch_replacement">Clutch Plate Overhaul</option>
                            <option value="oil_change">Engine Oil & Filter Change</option>
                            <option value="tire_change">Tire Replacement & Alignment</option>
                            <option value="body_repair">Denting / Painting / Repair</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Total Cost (₹) *</label>
                        <input type="number" step="0.01" name="cost" required placeholder="3500.00" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs font-semibold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Service Date *</label>
                        <input type="date" name="service_date" value="{{ now()->toDateString() }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Next Service Due Date</label>
                        <input type="date" name="next_service_due_date" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Workshop Name</label>
                        <input type="text" name="workshop_name" placeholder="Authorized Maruti Center" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Invoice / Job Card #</label>
                        <input type="text" name="invoice_number" placeholder="JOB-99128" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Service Notes / Work Done</label>
                    <textarea name="description" rows="2" placeholder="Details of parts replaced and service performed..." class="w-full form-textarea border border-slate-300 rounded px-2.5 py-1.5 text-xs"></textarea>
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="maintModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold">Record Maintenance</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
