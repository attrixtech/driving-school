@extends('layouts.app')

@section('content')
<div class="space-y-4">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Reports & Business Intelligence</h1>
            <p class="text-xs text-slate-500">Live operational audit and profitability analytics based on transactional database records.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.export.financial-csv') }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded text-xs font-medium shadow-xs transition-colors">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Export Financial Ledger CSV</span>
            </a>
        </div>
    </div>

    <!-- Profit & Loss Overview Box -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4">
        <h2 class="text-xs font-bold text-slate-800 pb-2 border-b border-slate-100 uppercase tracking-wider text-[11px] mb-3">
            Financial Health & Monthly Profitability
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
            <div class="p-3 bg-emerald-50/60 border border-emerald-200 rounded">
                <div class="text-[10px] uppercase font-bold text-emerald-800">Month Fee Collections</div>
                <div class="text-lg font-bold text-emerald-700 mt-1">₹{{ number_format($fin['month_collection'], 2) }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Total collected: ₹{{ number_format($fin['total_collection'], 2) }}</div>
            </div>

            <div class="p-3 bg-rose-50/60 border border-rose-200 rounded">
                <div class="text-[10px] uppercase font-bold text-rose-800">Month Operating Expenses</div>
                <div class="text-lg font-bold text-rose-700 mt-1">₹{{ number_format($fin['month_expenses'], 2) }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Total expenses: ₹{{ number_format($fin['total_expenses'], 2) }}</div>
            </div>

            <div class="p-3 bg-blue-50/60 border border-blue-200 rounded">
                <div class="text-[10px] uppercase font-bold text-blue-800">Estimated Net Profit</div>
                <div class="text-lg font-bold text-blue-700 mt-1">₹{{ number_format($fin['estimated_profit'], 2) }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Operating Margin</div>
            </div>

            <div class="p-3 bg-amber-50/60 border border-amber-200 rounded">
                <div class="text-[10px] uppercase font-bold text-amber-800">Outstanding Receivables</div>
                <div class="text-lg font-bold text-amber-700 mt-1">₹{{ number_format($fin['total_outstanding'], 2) }}</div>
                <div class="text-[10px] text-slate-500 mt-0.5">Total invoiced: ₹{{ number_format($fin['total_invoiced'], 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Course & Expense Analysis Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <!-- Course-wise Admissions Breakdown -->
        <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4">
            <h3 class="text-xs font-bold text-slate-800 pb-2 border-b border-slate-100">Course-wise Revenue & Admissions</h3>
            <div class="space-y-2 mt-3 text-xs">
                @foreach($courseStats as $cs)
                    <div class="flex items-center justify-between p-2.5 rounded bg-slate-50 border border-slate-100">
                        <div>
                            <div class="font-bold text-slate-900">{{ $cs->course?->name }}</div>
                            <div class="text-[10px] text-slate-400">{{ $cs->count }} student enrollments</div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-blue-700">₹{{ number_format($cs->total_revenue, 2) }}</div>
                            <span class="text-[10px] font-mono text-slate-400">Total Billed</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Expense Category Breakdown -->
        <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4">
            <h3 class="text-xs font-bold text-slate-800 pb-2 border-b border-slate-100">Overhead Expenses by Category</h3>
            <div class="space-y-2 mt-3 text-xs">
                @foreach($expenseCategories as $ec)
                    <div class="flex items-center justify-between p-2.5 rounded bg-slate-50 border border-slate-100">
                        <div class="capitalize font-bold text-slate-800">
                            {{ str_replace('_', ' ', $ec->expense_category) }}
                        </div>
                        <div class="font-bold text-rose-700">
                            ₹{{ number_format($ec->total, 2) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Instructor Performance & Fleet Utilization Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        
        <!-- Instructor Performance -->
        <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4">
            <h3 class="text-xs font-bold text-slate-800 pb-2 border-b border-slate-100">Instructor Productivity & Ratings</h3>
            <div class="overflow-x-auto mt-2">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                            <th class="py-1.5">Instructor</th>
                            <th class="py-1.5">Experience</th>
                            <th class="py-1.5">Completed Sessions</th>
                            <th class="py-1.5 text-right">Rating</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($instructors as $inst)
                            <tr>
                                <td class="py-2 font-medium text-slate-800">{{ $inst->name }}</td>
                                <td class="py-2 text-slate-500">{{ $inst->experience_years }} yrs</td>
                                <td class="py-2 font-bold text-slate-900">{{ $inst->completed_sessions }}</td>
                                <td class="py-2 text-right font-bold text-amber-600">&#9733; {{ number_format($inst->rating, 1) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Fleet Utilization -->
        <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4">
            <h3 class="text-xs font-bold text-slate-800 pb-2 border-b border-slate-100">Fleet Utilization & Running Costs</h3>
            <div class="overflow-x-auto mt-2">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="text-[10px] uppercase font-bold text-slate-400 border-b border-slate-100">
                            <th class="py-1.5">Vehicle Reg</th>
                            <th class="py-1.5">Odometer</th>
                            <th class="py-1.5">Sessions</th>
                            <th class="py-1.5 text-right">Fuel & Maint Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($vehicles as $veh)
                            <tr>
                                <td class="py-2 font-mono font-bold text-slate-800">{{ $veh->registration_number }}</td>
                                <td class="py-2 font-mono text-slate-600">{{ number_format($veh->odometer_reading) }} km</td>
                                <td class="py-2 font-semibold text-slate-800">{{ $veh->lesson_schedules_count }}</td>
                                <td class="py-2 text-right font-bold text-rose-700">
                                    ₹{{ number_format(($veh->total_fuel_cost ?? 0) + ($veh->total_maintenance_cost ?? 0), 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection
