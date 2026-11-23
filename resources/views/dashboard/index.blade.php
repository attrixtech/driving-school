@extends('layouts.app')

@section('content')
<div class="space-y-4">
    
    <!-- Top Welcome & Status Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Operations Dashboard</h1>
            <p class="text-xs text-slate-500">Live summary of driving training, student admissions, fleet utilization, and collections.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                System Live
            </span>
            <span class="text-xs text-slate-500">{{ now()->format('l, d F Y') }}</span>
        </div>
    </div>

    <!-- OPERATIONAL ALERTS (If any) -->
    @if(!empty($alerts))
    <div id="alerts-section" class="space-y-1.5">
        @foreach($alerts as $alert)
            <div class="flex items-center justify-between px-3 py-2 rounded border text-xs {{ $alert['type'] === 'danger' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-amber-50 border-amber-200 text-amber-800' }}">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0 {{ $alert['type'] === 'danger' ? 'text-rose-600' : 'text-amber-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div>
                        <span class="font-bold mr-1">{{ $alert['title'] }}:</span>
                        <span>{{ $alert['message'] }}</span>
                    </div>
                </div>
                <a href="{{ $alert['link'] }}" class="text-[11px] font-semibold underline hover:no-underline flex-shrink-0 ml-2">Resolve &rarr;</a>
            </div>
        @endforeach
    </div>
    @endif

    <!-- KEY STATS GRID (Compact, lightmode, dense) -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2.5">
        
        <!-- Total Students -->
        <div class="p-3 bg-white border border-slate-200 rounded-md shadow-xs">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-medium uppercase tracking-wider">Total Students</span>
                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <div class="text-lg font-bold text-slate-900">{{ $totalStudents }}</div>
            <div class="flex items-center justify-between text-[10px] text-slate-500 mt-0.5">
                <span>Active: <strong class="text-blue-600">{{ $activeStudents }}</strong></span>
                <span>Done: {{ $completedStudents }}</span>
            </div>
        </div>

        <!-- Today Admissions -->
        <div class="p-3 bg-white border border-slate-200 rounded-md shadow-xs">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-medium uppercase tracking-wider">Today Admissions</span>
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            </div>
            <div class="text-lg font-bold text-slate-900">{{ $newAdmissionsToday }}</div>
            <div class="text-[10px] text-emerald-600 mt-0.5 font-medium">New enrollments</div>
        </div>

        <!-- Today's Lessons -->
        <div class="p-3 bg-white border border-slate-200 rounded-md shadow-xs">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-medium uppercase tracking-wider">Today's Lessons</span>
                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div class="text-lg font-bold text-slate-900">{{ $todayScheduledCount }}</div>
            <div class="flex items-center justify-between text-[10px] text-slate-500 mt-0.5">
                <span>Completed: <strong class="text-emerald-600">{{ $todayCompletedCount }}</strong></span>
                <span>Missed: {{ $todayMissedCount }}</span>
            </div>
        </div>

        <!-- Fleet Status -->
        <div class="p-3 bg-white border border-slate-200 rounded-md shadow-xs">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-medium uppercase tracking-wider">Vehicles Active</span>
                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div class="text-lg font-bold text-slate-900">{{ $availableVehicles }} <span class="text-xs font-normal text-slate-400">/ {{ $totalVehicles }}</span></div>
            <div class="text-[10px] text-slate-500 mt-0.5">Dual-control fleet</div>
        </div>

        <!-- Month Revenue -->
        <div class="p-3 bg-white border border-slate-200 rounded-md shadow-xs">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-medium uppercase tracking-wider">Month Collection</span>
                <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="text-lg font-bold text-emerald-700">₹{{ number_format($fin['month_collection'], 2) }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5">Today: ₹{{ number_format($fin['today_collection'], 2) }}</div>
        </div>

        <!-- Outstanding Fees -->
        <div class="p-3 bg-white border border-slate-200 rounded-md shadow-xs">
            <div class="flex items-center justify-between text-slate-400 mb-1">
                <span class="text-[11px] font-medium uppercase tracking-wider">Fee Pending</span>
                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="text-lg font-bold text-rose-700">₹{{ number_format($fin['total_outstanding'], 2) }}</div>
            <div class="text-[10px] text-slate-500 mt-0.5">Net Profit: ₹{{ number_format($fin['estimated_profit'], 2) }}</div>
        </div>

    </div>

    <!-- MAIN TWO COLUMN SECTION -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <!-- LEFT 2 COLS: Today's Schedule & Quick Attendance -->
        <div class="lg:col-span-2 space-y-4">
            
            <div class="bg-white border border-slate-200 rounded-md shadow-xs">
                <div class="flex items-center justify-between px-3.5 py-2.5 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        <h2 class="text-xs font-bold text-slate-800">Today's Training Schedule & Attendance</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('schedules.create') }}" class="text-[11px] font-semibold text-blue-600 hover:text-blue-800">+ Schedule Lesson</a>
                        <a href="{{ route('schedules.calendar') }}" class="text-[11px] text-slate-500 hover:text-slate-800">View Calendar &rarr;</a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                                <th class="py-2 px-3">Slot Time</th>
                                <th class="py-2 px-3">Student</th>
                                <th class="py-2 px-3">Instructor</th>
                                <th class="py-2 px-3">Vehicle</th>
                                <th class="py-2 px-3">Topic</th>
                                <th class="py-2 px-3">Status</th>
                                <th class="py-2 px-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($todaySchedules as $sch)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-2 px-3 font-semibold text-slate-800 whitespace-nowrap">
                                        {{ \Carbon\Carbon::parse($sch->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($sch->end_time)->format('h:i A') }}
                                    </td>
                                    <td class="py-2 px-3">
                                        <a href="{{ route('students.show', $sch->student_id) }}" class="font-medium text-blue-600 hover:underline">
                                            {{ $sch->student?->full_name }}
                                        </a>
                                        <div class="text-[10px] text-slate-400">{{ $sch->student?->phone }}</div>
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        <span class="text-slate-700">{{ $sch->instructor?->name }}</span>
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        @if($sch->vehicle)
                                            <span class="px-1.5 py-0.5 bg-slate-100 text-slate-700 rounded text-[10px] font-mono border border-slate-200">
                                                {{ $sch->vehicle->registration_number }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic text-[11px]">Theory Room</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 max-w-[140px] truncate" title="{{ $sch->topic }}">
                                        {{ $sch->topic ?? 'Practical Lesson' }}
                                    </td>
                                    <td class="py-2 px-3 whitespace-nowrap">
                                        @if($sch->status === 'completed')
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Completed
                                            </span>
                                        @elseif($sch->status === 'scheduled')
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                Scheduled
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                                {{ ucfirst($sch->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-right whitespace-nowrap">
                                        @if($sch->status === 'scheduled')
                                            <a href="{{ route('schedules.complete-form', $sch->id) }}" class="px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-semibold">
                                                Record Attendance
                                            </a>
                                        @else
                                            <span class="text-[11px] text-slate-400">Done</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-4 text-center text-xs text-slate-400 italic">
                                        No training sessions scheduled for today. <a href="{{ route('schedules.create') }}" class="text-blue-600 font-semibold underline">Add one now</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Admissions Table -->
            <div class="bg-white border border-slate-200 rounded-md shadow-xs">
                <div class="flex items-center justify-between px-3.5 py-2.5 border-b border-slate-200">
                    <h2 class="text-xs font-bold text-slate-800">Recent Student Admissions</h2>
                    <a href="{{ route('admissions.create') }}" class="text-[11px] font-semibold text-blue-600 hover:text-blue-800">+ New Admission</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                                <th class="py-2 px-3">Adm #</th>
                                <th class="py-2 px-3">Student Name</th>
                                <th class="py-2 px-3">Course / Package</th>
                                <th class="py-2 px-3">Total Fee</th>
                                <th class="py-2 px-3">Payment</th>
                                <th class="py-2 px-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($recentAdmissions as $adm)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="py-2 px-3 font-mono text-[11px] font-semibold text-slate-800">
                                        {{ $adm->admission_number }}
                                    </td>
                                    <td class="py-2 px-3">
                                        <a href="{{ route('students.show', $adm->student_id) }}" class="font-medium text-blue-600 hover:underline">
                                            {{ $adm->student?->full_name }}
                                        </a>
                                        <div class="text-[10px] text-slate-400">{{ $adm->student?->phone }}</div>
                                    </td>
                                    <td class="py-2 px-3">
                                        <div class="font-medium text-slate-800">{{ $adm->course?->name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $adm->package?->name ?? 'Default Course' }}</div>
                                    </td>
                                    <td class="py-2 px-3 font-medium text-slate-800">
                                        ₹{{ number_format($adm->total_amount, 2) }}
                                    </td>
                                    <td class="py-2 px-3">
                                        @if($adm->payment_status === 'paid')
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Paid</span>
                                        @elseif($adm->payment_status === 'partially_paid')
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                Bal: ₹{{ number_format($adm->balance_amount, 2) }}
                                            </span>
                                        @else
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Unpaid</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-right whitespace-nowrap">
                                        <a href="{{ route('admissions.show', $adm->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-[11px]">View &rarr;</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-4 text-center text-xs text-slate-400 italic">No admissions recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- RIGHT 1 COL: Leads / Enquiries & Fleet Overview -->
        <div class="space-y-4">
            
            <!-- Quick Enquiries Followup -->
            <div class="bg-white border border-slate-200 rounded-md shadow-xs p-3.5">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200 mb-2.5">
                    <h2 class="text-xs font-bold text-slate-800">Recent Enquiries (CRM)</h2>
                    <a href="{{ route('crm.leads') }}" class="text-[11px] font-semibold text-blue-600 hover:text-blue-800">All Leads &rarr;</a>
                </div>

                <div class="space-y-2">
                    @forelse($recentLeads as $lead)
                        <div class="p-2 border border-slate-100 rounded bg-slate-50/50 hover:bg-slate-50 transition-colors text-xs">
                            <div class="flex items-center justify-between font-semibold text-slate-800">
                                <span>{{ $lead->full_name }}</span>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider {{ $lead->status === 'interested' ? 'bg-indigo-100 text-indigo-700' : ($lead->status === 'new' ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-700') }}">
                                    {{ $lead->status }}
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5 flex items-center justify-between">
                                <span>{{ $lead->phone }}</span>
                                <span class="text-slate-400">{{ $lead->course?->code ?? 'General' }}</span>
                            </div>
                            @if($lead->notes)
                                <div class="text-[10px] text-slate-600 mt-1 italic line-clamp-1">"{{ $lead->notes }}"</div>
                            @endif
                            <div class="mt-2 pt-1 border-t border-slate-200/60 flex items-center justify-between text-[11px]">
                                <span class="text-slate-400 text-[10px]">Follow up: {{ $lead->follow_up_date ? \Carbon\Carbon::parse($lead->follow_up_date)->format('d M') : 'None' }}</span>
                                <a href="{{ route('admissions.create') }}?lead_id={{ $lead->id }}" class="text-blue-600 font-semibold hover:underline text-[10px]">+ Convert to Admission</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-xs text-slate-400 py-3 italic">No enquiries captured yet.</div>
                    @endforelse
                </div>
            </div>

            <!-- Fleet & Instructor Status -->
            <div class="bg-white border border-slate-200 rounded-md shadow-xs p-3.5">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200 mb-2.5">
                    <h2 class="text-xs font-bold text-slate-800">Fleet & Resource Status</h2>
                    <a href="{{ route('vehicles.index') }}" class="text-[11px] font-semibold text-blue-600 hover:text-blue-800">Manage &rarr;</a>
                </div>

                <div class="space-y-2 text-xs">
                    @php
                        $vehicles = \App\Models\Vehicle::take(4)->get();
                    @endphp
                    @foreach($vehicles as $veh)
                        <div class="flex items-center justify-between p-2 rounded border border-slate-100 bg-white">
                            <div>
                                <div class="font-semibold text-slate-800 text-xs">{{ $veh->registration_number }}</div>
                                <div class="text-[10px] text-slate-500">{{ $veh->make }} {{ $veh->model }} ({{ ucfirst($veh->transmission) }})</div>
                            </div>
                            <div class="text-right">
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ ucfirst($veh->status) }}
                                </span>
                                <div class="text-[10px] font-mono text-slate-400 mt-0.5">{{ number_format($veh->odometer_reading) }} km</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
