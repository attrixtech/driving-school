@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-4">
    
    <!-- Top Bar with actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-base font-bold text-slate-900 tracking-tight">Admission #{{ $admission->admission_number }}</h1>
                @if($admission->status === 'active')
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">Active</span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase">{{ $admission->status }}</span>
                @endif
            </div>
            <p class="text-xs text-slate-500">Admitted on {{ $admission->admission_date->format('d M Y') }} at {{ $admission->branch?->name }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admissions.print', $admission->id) }}" target="_blank" class="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded text-xs font-semibold shadow-xs">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print Form</span>
            </a>
            <a href="{{ route('schedules.create') }}?student_id={{ $admission->student_id }}&admission_id={{ $admission->id }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Schedule Lesson</span>
            </a>
            <a href="{{ route('students.show', $admission->student_id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded text-xs font-semibold">
                Student Profile &rarr;
            </a>
        </div>
    </div>

    <!-- Student & Course Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
        
        <!-- Student Details Card -->
        <div class="bg-white border border-slate-200 rounded-md p-4 shadow-xs space-y-2.5">
            <h2 class="text-xs font-bold text-slate-800 pb-1.5 border-b border-slate-100 flex items-center justify-between">
                <span>Student Information</span>
                <span class="text-[10px] text-blue-600 font-mono">{{ $admission->student?->student_code }}</span>
            </h2>
            <div class="space-y-1.5 text-slate-600">
                <div class="flex justify-between">
                    <span class="text-slate-400">Full Name:</span>
                    <strong class="text-slate-900">{{ $admission->student?->full_name }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Phone:</span>
                    <span class="text-slate-900 font-medium">{{ $admission->student?->phone }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Email:</span>
                    <span>{{ $admission->student?->email ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Learner License (LL):</span>
                    <span class="font-mono text-slate-800 font-semibold">{{ $admission->student?->learner_license_number ?? 'Not Applied' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Assigned Instructor:</span>
                    <strong class="text-blue-700">{{ $admission->instructor?->name ?? 'Unassigned' }}</strong>
                </div>
            </div>
        </div>

        <!-- Course & Training Package Card -->
        <div class="bg-white border border-slate-200 rounded-md p-4 shadow-xs space-y-2.5">
            <h2 class="text-xs font-bold text-slate-800 pb-1.5 border-b border-slate-100 flex items-center justify-between">
                <span>Training Course Details</span>
                <span class="text-[10px] bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-mono">{{ $admission->course?->code }}</span>
            </h2>
            <div class="space-y-1.5 text-slate-600">
                <div class="flex justify-between">
                    <span class="text-slate-400">Course Name:</span>
                    <strong class="text-slate-900">{{ $admission->course?->name }}</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Package Selected:</span>
                    <span class="text-slate-900 font-medium">{{ $admission->package?->name ?? 'Standard Course' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Practical Sessions:</span>
                    <span class="font-bold text-slate-800">{{ $admission->package?->practical_sessions_count ?? $admission->course?->total_practical_hours }} Hours (1-on-1)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Theory Classes:</span>
                    <span class="font-bold text-slate-800">{{ $admission->package?->theory_sessions_count ?? $admission->course?->total_theory_hours }} Hours</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Expected End Date:</span>
                    <span>{{ $admission->expected_completion_date ? $admission->expected_completion_date->format('d M Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Financial Breakdown Card -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4 space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider text-[11px]">Fee Account & GST Invoice</h2>
            <div>
                @if($admission->payment_status === 'paid')
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">Fully Paid</span>
                @elseif($admission->payment_status === 'partially_paid')
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase">Partially Paid</span>
                @else
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 uppercase">Payment Pending</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center">
            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded">
                <div class="text-[10px] uppercase font-bold text-slate-400">Course Base Fee</div>
                <div class="text-sm font-bold text-slate-800 mt-0.5">₹{{ number_format($admission->course_fee, 2) }}</div>
            </div>
            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded">
                <div class="text-[10px] uppercase font-bold text-slate-400">Discount</div>
                <div class="text-sm font-bold text-emerald-600 mt-0.5">- ₹{{ number_format($admission->discount_amount, 2) }}</div>
            </div>
            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded">
                <div class="text-[10px] uppercase font-bold text-slate-400">GST (18%)</div>
                <div class="text-sm font-bold text-slate-800 mt-0.5">+ ₹{{ number_format($admission->tax_amount, 2) }}</div>
            </div>
            <div class="p-2.5 bg-blue-50 border border-blue-200 rounded">
                <div class="text-[10px] uppercase font-bold text-blue-600">Total Invoice</div>
                <div class="text-sm font-bold text-blue-800 mt-0.5">₹{{ number_format($admission->total_amount, 2) }}</div>
            </div>
            <div class="p-2.5 {{ $admission->balance_amount <= 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800' }} border rounded">
                <div class="text-[10px] uppercase font-bold">Balance Due</div>
                <div class="text-sm font-bold mt-0.5">₹{{ number_format($admission->balance_amount, 2) }}</div>
            </div>
        </div>

        <!-- Invoices & Receipts Tables -->
        <div class="pt-2">
            <h3 class="text-xs font-bold text-slate-800 mb-2">Invoices Generated</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                            <th class="py-1.5 px-3">Invoice No</th>
                            <th class="py-1.5 px-3">Date</th>
                            <th class="py-1.5 px-3">Total (inc. GST)</th>
                            <th class="py-1.5 px-3">Paid</th>
                            <th class="py-1.5 px-3">Balance</th>
                            <th class="py-1.5 px-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($admission->invoices as $inv)
                            <tr>
                                <td class="py-2 px-3 font-mono font-semibold text-slate-900">{{ $inv->invoice_number }}</td>
                                <td class="py-2 px-3">{{ $inv->invoice_date->format('d M Y') }}</td>
                                <td class="py-2 px-3 font-semibold">₹{{ number_format($inv->total_amount, 2) }}</td>
                                <td class="py-2 px-3 text-emerald-700">₹{{ number_format($inv->paid_amount, 2) }}</td>
                                <td class="py-2 px-3 font-semibold {{ $inv->balance_amount > 0 ? 'text-rose-600' : 'text-slate-500' }}">
                                    ₹{{ number_format($inv->balance_amount, 2) }}
                                </td>
                                <td class="py-2 px-3">
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $inv->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($admission->payments->count() > 0)
        <div class="pt-2">
            <h3 class="text-xs font-bold text-slate-800 mb-2">Payment Receipts</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                            <th class="py-1.5 px-3">Receipt No</th>
                            <th class="py-1.5 px-3">Date</th>
                            <th class="py-1.5 px-3">Amount</th>
                            <th class="py-1.5 px-3">Payment Mode</th>
                            <th class="py-1.5 px-3">Transaction Ref</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($admission->payments as $pmt)
                            <tr>
                                <td class="py-2 px-3 font-mono font-semibold text-slate-900">{{ $pmt->receipt_number }}</td>
                                <td class="py-2 px-3">{{ $pmt->payment_date->format('d M Y') }}</td>
                                <td class="py-2 px-3 font-bold text-emerald-700">₹{{ number_format($pmt->amount, 2) }}</td>
                                <td class="py-2 px-3 uppercase text-[10px] font-semibold text-slate-700">{{ $pmt->payment_mode }}</td>
                                <td class="py-2 px-3 text-slate-500 font-mono">{{ $pmt->transaction_reference ?? 'Cash Deposit' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

</div>
@endsection
