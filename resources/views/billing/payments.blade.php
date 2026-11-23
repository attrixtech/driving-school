@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ collectModal: {{ request()->has('collect') ? 'true' : 'false' }} }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Fee Collections & Receipts</h1>
            <p class="text-xs text-slate-500">Record cash, UPI, card, and online fee installments with automated receipt generation.</p>
        </div>
        <button @click="collectModal = true" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Collect Fee / Installment</span>
        </button>
    </div>

    <!-- Payments Table -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3">Receipt Number</th>
                        <th class="py-2 px-3">Date</th>
                        <th class="py-2 px-3">Student Name</th>
                        <th class="py-2 px-3">Amount Collected</th>
                        <th class="py-2 px-3">Mode</th>
                        <th class="py-2 px-3">Transaction Ref</th>
                        <th class="py-2 px-3">Branch</th>
                        <th class="py-2 px-3">Cashier / Staff</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $pmt)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $pmt->receipt_number }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap text-slate-600">
                                {{ $pmt->payment_date->format('d M Y') }}
                            </td>
                            <td class="py-2.5 px-3">
                                <a href="{{ route('students.show', $pmt->student_id) }}" class="font-bold text-blue-600 hover:underline">
                                    {{ $pmt->student?->full_name }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $pmt->student?->student_code }}</div>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap font-bold text-emerald-700">
                                ₹{{ number_format($pmt->amount, 2) }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $pmt->payment_mode }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap font-mono text-[11px] text-slate-500">
                                {{ $pmt->transaction_reference ?? 'Cash In Hand' }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-600">
                                {{ $pmt->branch?->name }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-700">
                                {{ $pmt->receivedBy?->name ?? 'Staff' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-xs text-slate-400 italic">No payments collected yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="px-3 py-2 border-t border-slate-200 bg-slate-50">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

    <!-- COLLECT FEE MODAL -->
    <div x-show="collectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="collectModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Record Fee Collection</h3>
                <button @click="collectModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('billing.payments.store') }}" class="p-4 space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Select Student *</label>
                    <select name="student_id" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        <option value="">-- Choose Student --</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}">{{ $s->full_name }} ({{ $s->student_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Link to Unpaid Invoice (Optional)</label>
                    <select name="invoice_id" class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        <option value="">-- Auto-allocate or General Installment --</option>
                        @foreach($pendingInvoices as $pi)
                            <option value="{{ $pi->id }}">
                                {{ $pi->invoice_number }} - {{ $pi->student?->full_name }} (Balance: ₹{{ number_format($pi->balance_amount, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Payment Amount (₹) *</label>
                        <input type="number" step="0.01" name="amount" required placeholder="3000.00" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs font-bold text-slate-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Payment Date *</label>
                        <input type="date" name="payment_date" value="{{ now()->toDateString() }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Payment Mode *</label>
                        <select name="payment_mode" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="cash">Cash Payment</option>
                            <option value="upi">UPI (GPay, PhonePe, Paytm)</option>
                            <option value="card">Credit / Debit Card</option>
                            <option value="bank_transfer">Bank Transfer / NEFT</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Transaction Ref / Cheque #</label>
                        <input type="text" name="transaction_reference" placeholder="UPI-123456" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Receipt Notes</label>
                    <input type="text" name="notes" placeholder="Part payment installment 2..." class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="collectModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold">Generate Receipt</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
