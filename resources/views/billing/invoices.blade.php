@extends('layouts.app')

@section('content')
<div class="space-y-4">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">GST Invoices & Billing</h1>
            <p class="text-xs text-slate-500">Tax invoices issued to driving students with automated 18% GST (CGST + SGST) breakdown.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('billing.payments') }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                + Collect Fee &rarr;
            </a>
        </div>
    </div>

    <!-- Filter status tabs -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
        <a href="{{ route('billing.invoices') }}" class="px-2.5 py-1 rounded font-medium transition-colors {{ empty($status) ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            All Invoices
        </a>
        <a href="{{ route('billing.invoices', ['status' => 'unpaid']) }}" class="px-2.5 py-1 rounded font-medium transition-colors {{ $status === 'unpaid' ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Unpaid
        </a>
        <a href="{{ route('billing.invoices', ['status' => 'partially_paid']) }}" class="px-2.5 py-1 rounded font-medium transition-colors {{ $status === 'partially_paid' ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Partially Paid
        </a>
        <a href="{{ route('billing.invoices', ['status' => 'paid']) }}" class="px-2.5 py-1 rounded font-medium transition-colors {{ $status === 'paid' ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Fully Paid
        </a>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3">Invoice Number</th>
                        <th class="py-2 px-3">Date</th>
                        <th class="py-2 px-3">Student Name</th>
                        <th class="py-2 px-3">Subtotal</th>
                        <th class="py-2 px-3">GST (18%)</th>
                        <th class="py-2 px-3">Total Amount</th>
                        <th class="py-2 px-3">Paid</th>
                        <th class="py-2 px-3">Balance Due</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $inv->invoice_number }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap text-slate-600">
                                {{ $inv->invoice_date->format('d M Y') }}
                            </td>
                            <td class="py-2.5 px-3">
                                <a href="{{ route('students.show', $inv->student_id) }}" class="font-bold text-blue-600 hover:underline">
                                    {{ $inv->student?->full_name }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $inv->student?->student_code }}</div>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">₹{{ number_format($inv->subtotal, 2) }}</td>
                            <td class="py-2.5 px-3 whitespace-nowrap text-slate-600">₹{{ number_format($inv->tax_amount, 2) }}</td>
                            <td class="py-2.5 px-3 whitespace-nowrap font-bold text-slate-900">₹{{ number_format($inv->total_amount, 2) }}</td>
                            <td class="py-2.5 px-3 whitespace-nowrap font-medium text-emerald-700">₹{{ number_format($inv->paid_amount, 2) }}</td>
                            <td class="py-2.5 px-3 whitespace-nowrap font-bold {{ $inv->balance_amount > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                ₹{{ number_format($inv->balance_amount, 2) }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($inv->status === 'partially_paid' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ str_replace('_', ' ', $inv->status) }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                <a href="{{ route('billing.invoices.show', $inv->id) }}" target="_blank" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-semibold border border-slate-200">
                                    View GST Invoice &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-6 text-center text-xs text-slate-400 italic">No invoices found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="px-3 py-2 border-t border-slate-200 bg-slate-50">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
