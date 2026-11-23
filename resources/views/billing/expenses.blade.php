@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ newExpModal: {{ request()->has('new') ? 'true' : 'false' }} }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Operating Expenses</h1>
            <p class="text-xs text-slate-500">Record branch overheads, vehicle maintenance, fuel, utility bills, and RTO official fees.</p>
        </div>
        <button @click="newExpModal = true" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Record Expense</span>
        </button>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3">Date</th>
                        <th class="py-2 px-3">Category</th>
                        <th class="py-2 px-3">Description / Title</th>
                        <th class="py-2 px-3">Amount</th>
                        <th class="py-2 px-3">Paid To</th>
                        <th class="py-2 px-3">Vehicle</th>
                        <th class="py-2 px-3">Payment Mode</th>
                        <th class="py-2 px-3">Reference #</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $exp)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3 whitespace-nowrap font-medium text-slate-800">
                                {{ $exp->expense_date->format('d M Y') }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $exp->expense_category }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 font-semibold text-slate-900">
                                {{ $exp->title }}
                            </td>
                            <td class="py-2.5 px-3 font-bold text-rose-700 whitespace-nowrap">
                                ₹{{ number_format($exp->amount, 2) }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 whitespace-nowrap">
                                {{ $exp->paid_to ?? 'Vendor' }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap font-mono text-[11px]">
                                {{ $exp->vehicle?->registration_number ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap uppercase text-[10px] text-slate-500">
                                {{ $exp->payment_mode }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap font-mono text-slate-400">
                                {{ $exp->reference_number ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-xs text-slate-400 italic">No expenses recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="px-3 py-2 border-t border-slate-200 bg-slate-50">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    <!-- NEW EXPENSE MODAL -->
    <div x-show="newExpModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="newExpModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Record Operating Expense</h3>
                <button @click="newExpModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('billing.expenses.store') }}" class="p-4 space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Expense Category *</label>
                    <select name="expense_category" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        <option value="fuel">Vehicle Fuel (Petrol / Diesel / CNG)</option>
                        <option value="maintenance">Vehicle Servicing & Repairs</option>
                        <option value="rent">Branch Office Rent</option>
                        <option value="electricity">Electricity & Utilities</option>
                        <option value="salary">Staff & Instructor Salary</option>
                        <option value="rto_fee">RTO Official Government Fees</option>
                        <option value="marketing">Marketing & Advertising</option>
                        <option value="office_supplies">Office Supplies & Stationery</option>
                        <option value="other">Other Miscellaneous</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Expense Title / Description *</label>
                    <input type="text" name="title" required placeholder="e.g. BESCOM Office Electricity Bill for Oct" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Amount (₹) *</label>
                        <input type="number" step="0.01" name="amount" required placeholder="1500.00" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs font-bold text-slate-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Expense Date *</label>
                        <input type="date" name="expense_date" value="{{ now()->toDateString() }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Vehicle (If vehicle expense)</label>
                        <select name="vehicle_id" class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="">-- Non-vehicle expense --</option>
                            @foreach($vehicles as $v)
                                <option value="{{ $v->id }}">{{ $v->registration_number }} - {{ $v->model }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Payment Mode *</label>
                        <select name="payment_mode" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI</option>
                            <option value="bank_transfer">Bank Transfer / NEFT</option>
                            <option value="card">Card</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Paid To / Payee</label>
                        <input type="text" name="paid_to" placeholder="BESCOM / Landlord / Vendor" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Bill / Reference Number</label>
                        <input type="text" name="reference_number" placeholder="Ref-9912" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="newExpModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold">Save Expense</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
