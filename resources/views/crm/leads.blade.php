@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ 
    newLeadModal: {{ request()->has('new') ? 'true' : 'false' }}, 
    followupModal: false, 
    viewLogsModal: false,
    activeLead: null,
    expandedLeads: {}
}">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Enquiries & Leads CRM</h1>
            <p class="text-xs text-slate-500">Capture, follow up, inspect call logs, and convert prospects into admitted students.</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="newLeadModal = true" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ New Enquiry</span>
            </button>
        </div>
    </div>

    <!-- Telemetry Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-2.5">
        <div class="p-2.5 bg-white border border-slate-200 rounded shadow-xs">
            <div class="text-[10px] uppercase font-bold text-slate-400">Total Inquiries</div>
            <div class="text-base font-bold text-slate-800 mt-0.5">{{ $stats['total_leads'] }}</div>
            <div class="text-[10px] text-slate-400">All registered leads</div>
        </div>
        <div class="p-2.5 bg-white border border-slate-200 rounded shadow-xs">
            <div class="text-[10px] uppercase font-bold text-slate-400">Follow-ups Today</div>
            <div class="text-base font-bold text-blue-700 mt-0.5">{{ $stats['followups_due_today'] }}</div>
            <div class="text-[10px] text-blue-600 font-medium">Scheduled calls</div>
        </div>
        <div class="p-2.5 bg-white border border-slate-200 rounded shadow-xs">
            <div class="text-[10px] uppercase font-bold text-slate-400">Overdue Follow-ups</div>
            <div class="text-base font-bold {{ $stats['overdue_followups'] > 0 ? 'text-rose-700' : 'text-slate-800' }} mt-0.5">{{ $stats['overdue_followups'] }}</div>
            <div class="text-[10px] {{ $stats['overdue_followups'] > 0 ? 'text-rose-600 font-semibold' : 'text-slate-400' }}">Requires immediate call</div>
        </div>
        <div class="p-2.5 bg-white border border-slate-200 rounded shadow-xs">
            <div class="text-[10px] uppercase font-bold text-slate-400">Total Call Logs</div>
            <div class="text-base font-bold text-emerald-700 mt-0.5">{{ $stats['total_call_logs'] }}</div>
            <div class="text-[10px] text-emerald-600 font-medium">Recorded interactions</div>
        </div>
        <div class="p-2.5 bg-white border border-slate-200 rounded shadow-xs">
            <div class="text-[10px] uppercase font-bold text-slate-400">Converted Admissions</div>
            <div class="text-base font-bold text-purple-700 mt-0.5">{{ $stats['admitted'] }}</div>
            <div class="text-[10px] text-slate-400">Enrolled students</div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white border border-slate-200 rounded-md p-2.5 shadow-xs space-y-2">
        <form method="GET" action="{{ route('crm.leads') }}" class="flex flex-col sm:flex-row items-center gap-2">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by prospect name, phone, lead ref (e.g. LEAD-2026-001), email, or city..." class="w-full pl-8 border border-slate-300 rounded px-2.5 py-1.5 text-xs focus:border-blue-500 focus:outline-none">
                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            @if(!empty($status))
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            @if(!empty($filter))
                <input type="hidden" name="filter" value="{{ $filter }}">
            @endif
            <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium transition-colors">
                Search
            </button>
            @if(!empty($search) || !empty($status) || !empty($filter))
                <a href="{{ route('crm.leads') }}" class="px-2.5 py-1.5 text-slate-500 hover:text-slate-700 text-xs">Clear</a>
            @endif
        </form>

        <!-- Status & Due Date Filter Pills -->
        <div class="flex flex-wrap items-center gap-1.5 pt-1 border-t border-slate-100 text-xs">
            <span class="text-[10px] uppercase font-bold text-slate-400 mr-1">Status:</span>
            <a href="{{ route('crm.leads', array_filter(['search' => $search])) }}" class="px-2 py-0.5 rounded text-[11px] font-medium transition-colors {{ empty($status) && empty($filter) ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All Leads
            </a>
            <a href="{{ route('crm.leads', array_filter(['search' => $search, 'filter' => 'today'])) }}" class="px-2 py-0.5 rounded text-[11px] font-medium transition-colors {{ $filter === 'today' ? 'bg-blue-600 text-white font-semibold' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200' }}">
                📞 Due Today ({{ $stats['followups_due_today'] }})
            </a>
            <a href="{{ route('crm.leads', array_filter(['search' => $search, 'filter' => 'overdue'])) }}" class="px-2 py-0.5 rounded text-[11px] font-medium transition-colors {{ $filter === 'overdue' ? 'bg-rose-600 text-white font-semibold' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' }}">
                ⚠️ Overdue ({{ $stats['overdue_followups'] }})
            </a>
            <span class="text-slate-300 mx-1">|</span>
            <a href="{{ route('crm.leads', array_filter(['search' => $search, 'status' => 'new'])) }}" class="px-2 py-0.5 rounded text-[11px] font-medium transition-colors {{ $status === 'new' ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                New
            </a>
            <a href="{{ route('crm.leads', array_filter(['search' => $search, 'status' => 'contacted'])) }}" class="px-2 py-0.5 rounded text-[11px] font-medium transition-colors {{ $status === 'contacted' ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Contacted
            </a>
            <a href="{{ route('crm.leads', array_filter(['search' => $search, 'status' => 'interested'])) }}" class="px-2 py-0.5 rounded text-[11px] font-medium transition-colors {{ $status === 'interested' ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Interested
            </a>
            <a href="{{ route('crm.leads', array_filter(['search' => $search, 'status' => 'admitted'])) }}" class="px-2 py-0.5 rounded text-[11px] font-medium transition-colors {{ $status === 'admitted' ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Admitted
            </a>
            <a href="{{ route('crm.leads', array_filter(['search' => $search, 'status' => 'lost'])) }}" class="px-2 py-0.5 rounded text-[11px] font-medium transition-colors {{ $status === 'lost' ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Lost
            </a>
        </div>
    </div>

    <!-- Leads Table with Direct Call Logs Integration -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3 w-10 text-center">Logs</th>
                        <th class="py-2 px-3">Lead Ref & Date</th>
                        <th class="py-2 px-3">Prospect Contact</th>
                        <th class="py-2 px-3">Course / Source</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3">Follow-up Due</th>
                        <th class="py-2 px-3">Call Log History & Latest Activity</th>
                        <th class="py-2 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $lead->followups->count() > 0 ? 'bg-white' : '' }}">
                            
                            <!-- Expand Accordion Toggle -->
                            <td class="py-2 px-3 text-center">
                                <button type="button" 
                                        @click="expandedLeads[{{ $lead->id }}] = !expandedLeads[{{ $lead->id }}]" 
                                        class="p-1 rounded text-slate-400 hover:text-blue-600 hover:bg-slate-100 transition-colors"
                                        title="Click to toggle call log history">
                                    <svg class="w-3.5 h-3.5 transform transition-transform duration-150" 
                                         :class="expandedLeads[{{ $lead->id }}] ? 'rotate-90 text-blue-600' : ''" 
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            </td>

                            <!-- Lead Ref & Creation Date -->
                            <td class="py-2 px-3 whitespace-nowrap">
                                <div class="font-mono text-[11px] font-bold text-slate-800">{{ $lead->lead_number }}</div>
                                <div class="text-[10px] text-slate-400">{{ $lead->created_at->format('d M Y') }}</div>
                            </td>

                            <!-- Prospect Contact -->
                            <td class="py-2 px-3">
                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <span>{{ $lead->full_name }}</span>
                                    @if($lead->city)
                                        <span class="text-[9px] px-1 py-0.2 bg-slate-100 text-slate-600 rounded font-normal">{{ $lead->city }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px]">
                                    <a href="tel:{{ $lead->phone }}" class="text-blue-600 hover:text-blue-800 font-medium flex items-center gap-0.5" title="Click to Call">
                                        <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                        <span>{{ $lead->phone }}</span>
                                    </a>
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $lead->phone);
                                        if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="text-emerald-600 hover:text-emerald-800" title="WhatsApp Chat">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zm5.79 14.05c-.24.67-1.39 1.28-1.92 1.36-.5.08-1.14.12-3.32-.78-2.61-1.08-4.28-3.74-4.41-3.91-.13-.18-1.06-1.41-1.06-2.69 0-1.28.67-1.91.91-2.17.24-.26.53-.33.7-.33.18 0 .36 0 .52.01.17.01.39-.06.61.47.23.55.78 1.9.85 2.04.07.15.11.32.02.5-.09.18-.14.29-.28.45-.14.16-.29.35-.42.47-.14.14-.29.3-.13.58.17.28.74 1.22 1.59 1.97 1.09.97 2.01 1.27 2.3 1.41.28.14.45.12.62-.07.17-.19.72-.84.91-1.13.19-.29.38-.24.64-.15.26.09 1.66.78 1.94.92.29.14.48.21.55.33.07.11.07.67-.17 1.34z"/></svg>
                                    </a>
                                </div>
                                @if($lead->email)
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $lead->email }}</div>
                                @endif
                            </td>

                            <!-- Course / Source -->
                            <td class="py-2 px-3">
                                <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 font-medium rounded text-[11px] border border-blue-100 block w-fit">
                                    {{ $lead->course?->name ?? 'General Inquiry' }}
                                </span>
                                <span class="text-[10px] text-slate-400 capitalize mt-0.5 block">
                                    via {{ str_replace('_', ' ', $lead->source) }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="py-2 px-3 whitespace-nowrap">
                                @php
                                    $statusClasses = [
                                        'new' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'contacted' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'interested' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'trial_booked' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'admitted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'lost' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold uppercase tracking-wider border {{ $statusClasses[$lead->status] ?? 'bg-slate-100' }}">
                                    {{ str_replace('_', ' ', $lead->status) }}
                                </span>
                            </td>

                            <!-- Follow-up Due -->
                            <td class="py-2 px-3 whitespace-nowrap">
                                @if($lead->follow_up_date)
                                    @php
                                        $fDate = \Carbon\Carbon::parse($lead->follow_up_date);
                                        $isToday = $fDate->isToday();
                                        $isPast = $fDate->isPast() && !$isToday;
                                    @endphp
                                    <div class="font-medium {{ $isPast ? 'text-rose-600 font-bold' : ($isToday ? 'text-amber-600 font-bold' : 'text-slate-700') }}">
                                        {{ $fDate->format('d M Y') }}
                                        @if($isToday)
                                            <span class="text-[9px] px-1 py-0.2 bg-amber-100 text-amber-800 rounded font-bold ml-0.5">Today</span>
                                        @elseif($isPast && !in_array($lead->status, ['admitted', 'lost']))
                                            <span class="text-[9px] px-1 py-0.2 bg-rose-100 text-rose-800 rounded font-bold ml-0.5">Overdue</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">None set</span>
                                @endif
                            </td>

                            <!-- Call History Summary & Expand Button -->
                            <td class="py-2 px-3">
                                <div class="flex items-center gap-1.5">
                                    <button type="button" 
                                            @click="expandedLeads[{{ $lead->id }}] = !expandedLeads[{{ $lead->id }}]"
                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold border transition-colors {{ $lead->followups->count() > 0 ? 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100' : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100' }}">
                                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                        <span>{{ $lead->followups->count() }} {{ \Illuminate\Support\Str::plural('Log', $lead->followups->count()) }}</span>
                                    </button>

                                    @if($lead->followups->count() > 0)
                                        @php $latest = $lead->followups->first(); @endphp
                                        <span class="text-[10px] text-slate-500 truncate max-w-[200px]" title="{{ $latest->notes }}">
                                            <strong>{{ ucfirst($latest->communication_channel) }} ({{ $latest->outcome }}):</strong> {{ $latest->notes }}
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">No calls logged yet</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="py-2 px-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- View Call Logs Button -->
                                    <button type="button" 
                                            @click="activeLead = {{ $lead->toJson() }}; viewLogsModal = true" 
                                            class="px-2 py-0.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded text-[11px] font-medium border border-blue-200 flex items-center gap-1 transition-colors"
                                            title="View full conversation history">
                                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <span>View Logs</span>
                                    </button>

                                    <!-- Log Call Button -->
                                    <button type="button" 
                                            @click="activeLead = {{ $lead->toJson() }}; followupModal = true" 
                                            class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-medium border border-slate-200 flex items-center gap-1 transition-colors">
                                        <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        <span>Log Call</span>
                                    </button>

                                    <!-- Admit Button -->
                                    @if($lead->status !== 'admitted')
                                        <a href="{{ route('admissions.create') }}?lead_id={{ $lead->id }}" class="px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-semibold transition-colors">
                                            Admit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>

                        <!-- INLINE EXPANDED CALL LOGS TIMELINE -->
                        <tr x-show="expandedLeads[{{ $lead->id }}]" x-cloak class="bg-slate-50/70 border-b border-slate-200">
                            <td colspan="8" class="p-3">
                                <div class="bg-white rounded border border-slate-200 p-3 shadow-xs">
                                    
                                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-2.5">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                            <span class="text-xs font-bold text-slate-800">
                                                Call Logs & Interaction History: {{ $lead->full_name }} ({{ $lead->lead_number }})
                                            </span>
                                            <span class="text-[10px] font-semibold px-1.5 py-0.2 bg-blue-50 text-blue-700 rounded border border-blue-100">
                                                {{ $lead->followups->count() }} Total {{ \Illuminate\Support\Str::plural('Entry', $lead->followups->count()) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" 
                                                    @click="activeLead = {{ $lead->toJson() }}; followupModal = true" 
                                                    class="px-2 py-0.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-[10px] font-semibold flex items-center gap-1 transition-colors">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                <span>+ Record New Call / Interaction</span>
                                            </button>
                                            <button type="button" @click="expandedLeads[{{ $lead->id }}] = false" class="text-slate-400 hover:text-slate-600 text-[10px]">
                                                ✕ Close
                                            </button>
                                        </div>
                                    </div>

                                    @if($lead->followups->count() > 0)
                                        <div class="space-y-2">
                                            @foreach($lead->followups as $log)
                                                @php
                                                    $channelBadges = [
                                                        'call' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'label' => 'Phone Call', 'icon' => '📞'],
                                                        'whatsapp' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'WhatsApp', 'icon' => '💬'],
                                                        'in_person' => ['bg' => 'bg-purple-50 text-purple-700 border-purple-200', 'label' => 'In-Person Visit', 'icon' => '👤'],
                                                        'email' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Email', 'icon' => '✉️'],
                                                        'sms' => ['bg' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => 'SMS', 'icon' => '📱'],
                                                    ];
                                                    $outcomeBadges = [
                                                        'connected' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                        'callback_requested' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                        'no_answer' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                        'enrolled' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                        'not_interested' => 'bg-slate-100 text-slate-600 border-slate-200',
                                                    ];
                                                    $ch = $channelBadges[$log->communication_channel] ?? ['bg' => 'bg-slate-50', 'label' => $log->communication_channel, 'icon' => '📝'];
                                                @endphp
                                                <div class="p-2.5 rounded border border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row sm:items-start justify-between gap-2 text-xs">
                                                    <div class="space-y-1 flex-1">
                                                        <div class="flex flex-wrap items-center gap-1.5">
                                                            <!-- Channel badge -->
                                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-bold border {{ $ch['bg'] }}">
                                                                <span>{{ $ch['icon'] }}</span>
                                                                <span>{{ $ch['label'] }}</span>
                                                            </span>

                                                            <!-- Outcome badge -->
                                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold border uppercase tracking-wider {{ $outcomeBadges[$log->outcome] ?? 'bg-slate-100' }}">
                                                                {{ str_replace('_', ' ', $log->outcome) }}
                                                            </span>

                                                            <!-- Timestamp -->
                                                            <span class="text-[10px] text-slate-400 font-mono">
                                                                {{ $log->followup_at ? $log->followup_at->format('d M Y, h:i A') : $log->created_at->format('d M Y, h:i A') }}
                                                                ({{ $log->followup_at ? $log->followup_at->diffForHumans() : $log->created_at->diffForHumans() }})
                                                            </span>

                                                            <!-- Staff name -->
                                                            <span class="text-[10px] text-slate-500">
                                                                • Logged by <strong>{{ $log->user?->name ?? 'Staff' }}</strong>
                                                            </span>
                                                        </div>

                                                        <!-- Call Notes -->
                                                        <div class="text-[11px] text-slate-800 bg-white p-2 rounded border border-slate-200/80 mt-1 whitespace-pre-line leading-relaxed">
                                                            {{ $log->notes }}
                                                        </div>
                                                    </div>

                                                    @if($log->next_followup_date)
                                                        <div class="sm:text-right shrink-0">
                                                            <span class="text-[9px] uppercase font-bold text-slate-400 block">Next Follow-up</span>
                                                            <span class="text-[11px] font-semibold text-blue-700">
                                                                {{ \Carbon\Carbon::parse($log->next_followup_date)->format('d M Y') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="py-4 text-center text-xs text-slate-400 italic">
                                            No call interactions or follow-up notes logged for this prospect yet.
                                            <button type="button" @click="activeLead = {{ $lead->toJson() }}; followupModal = true" class="text-blue-600 underline hover:no-underline font-medium ml-1">
                                                Log the first call now &rarr;
                                            </button>
                                        </div>
                                    @endif

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-xs text-slate-400 italic">
                                No leads found matching criteria. Click "+ New Enquiry" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
            <div class="px-3 py-2 border-t border-slate-200 bg-slate-50">
                {{ $leads->links() }}
            </div>
        @endif
    </div>

    <!-- DEDICATED VIEW CALL LOGS MODAL -->
    <div x-show="viewLogsModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="viewLogsModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-2xl overflow-hidden border border-slate-200 flex flex-col max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                        <span>Call Logs & Communication History</span>
                        <span class="font-mono text-[10px] bg-slate-200 text-slate-700 px-1.5 py-0.2 rounded" x-text="activeLead ? activeLead.lead_number : ''"></span>
                    </h3>
                    <p class="text-[10px] text-slate-500 mt-0.5">
                        Prospect: <strong class="text-slate-700" x-text="activeLead ? activeLead.full_name : ''"></strong> | 
                        Course: <span class="text-slate-600" x-text="activeLead && activeLead.course ? activeLead.course.name : 'General Inquiry'"></span>
                    </p>
                </div>
                <button @click="viewLogsModal = false" class="text-slate-400 hover:text-slate-600 text-base font-bold">&times;</button>
            </div>

            <!-- Contact Quick Actions Bar -->
            <div class="px-4 py-2 bg-blue-50/50 border-b border-slate-200 flex items-center justify-between text-xs">
                <div class="flex items-center gap-3">
                    <span class="text-[11px] font-medium text-slate-700 flex items-center gap-1">
                        📞 <strong x-text="activeLead ? activeLead.phone : ''"></strong>
                    </span>
                    <a :href="'tel:' + (activeLead ? activeLead.phone : '')" class="text-[11px] font-semibold text-blue-600 hover:text-blue-800 underline">
                        Call Now
                    </a>
                    <a :href="'https://wa.me/' + (activeLead ? activeLead.phone.replace(/[^0-9]/g, '') : '')" target="_blank" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-800 flex items-center gap-1">
                        <span>💬 WhatsApp</span>
                    </a>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400">Status: </span>
                    <span class="font-bold text-[10px] uppercase text-blue-700" x-text="activeLead ? activeLead.status : ''"></span>
                </div>
            </div>

            <!-- Scrollable Logs Timeline -->
            <div class="p-4 overflow-y-auto space-y-3 flex-1 text-xs">
                <template x-if="activeLead && activeLead.followups && activeLead.followups.length > 0">
                    <div class="space-y-2.5">
                        <template x-for="log in activeLead.followups" :key="log.id">
                            <div class="p-3 rounded border border-slate-200 bg-slate-50/70 space-y-1.5">
                                <div class="flex flex-wrap items-center justify-between gap-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase tracking-wider border"
                                              :class="{
                                                  'bg-blue-50 text-blue-700 border-blue-200': log.communication_channel === 'call',
                                                  'bg-emerald-50 text-emerald-700 border-emerald-200': log.communication_channel === 'whatsapp',
                                                  'bg-purple-50 text-purple-700 border-purple-200': log.communication_channel === 'in_person',
                                                  'bg-amber-50 text-amber-700 border-amber-200': log.communication_channel === 'email',
                                                  'bg-slate-100 text-slate-700 border-slate-200': log.communication_channel === 'sms'
                                              }">
                                            <span x-text="log.communication_channel === 'call' ? '📞 Phone Call' : (log.communication_channel === 'whatsapp' ? '💬 WhatsApp' : (log.communication_channel === 'in_person' ? '👤 In-Person' : log.communication_channel))"></span>
                                        </span>
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase tracking-wider border"
                                              :class="{
                                                  'bg-emerald-50 text-emerald-700 border-emerald-200': log.outcome === 'connected' || log.outcome === 'enrolled',
                                                  'bg-amber-50 text-amber-700 border-amber-200': log.outcome === 'callback_requested',
                                                  'bg-rose-50 text-rose-700 border-rose-200': log.outcome === 'no_answer',
                                                  'bg-slate-100 text-slate-700 border-slate-200': log.outcome === 'not_interested'
                                              }"
                                              x-text="log.outcome ? log.outcome.replace('_', ' ') : ''">
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        <span x-text="new Date(log.followup_at || log.created_at).toLocaleString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })"></span>
                                        <span x-show="log.user" class="text-slate-500 font-sans">• Staff: <strong x-text="log.user ? log.user.name : ''"></strong></span>
                                    </div>
                                </div>
                                <div class="p-2 bg-white rounded border border-slate-200 text-[11px] text-slate-800 leading-relaxed whitespace-pre-line" x-text="log.notes"></div>
                                <div x-show="log.next_followup_date" class="text-[10px] text-slate-500">
                                    Next Follow-up Scheduled: <strong class="text-blue-700" x-text="log.next_followup_date ? new Date(log.next_followup_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : ''"></strong>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!activeLead || !activeLead.followups || activeLead.followups.length === 0">
                    <div class="py-8 text-center text-slate-400 italic">
                        No previous call interactions recorded for this lead.
                    </div>
                </template>

                <!-- Embedded Log Next Call Form -->
                <div class="mt-4 pt-3 border-t border-slate-200">
                    <h4 class="text-[11px] font-bold text-slate-800 mb-2">+ Record Next Follow-up Call / Interaction:</h4>
                    <form :action="'/crm/leads/' + (activeLead ? activeLead.id : 0) + '/followup'" method="POST" class="space-y-2.5">
                        @csrf
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-0.5">Channel *</label>
                                <select name="communication_channel" required class="w-full border border-slate-300 rounded px-2 py-1 text-xs">
                                    <option value="call">Phone Call</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="in_person">In-Person Visit</option>
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-0.5">Outcome *</label>
                                <select name="outcome" required class="w-full border border-slate-300 rounded px-2 py-1 text-xs">
                                    <option value="connected">Connected & Discussed</option>
                                    <option value="callback_requested">Callback Requested</option>
                                    <option value="no_answer">No Answer / Busy</option>
                                    <option value="enrolled">Agreed to Enroll</option>
                                    <option value="not_interested">Not Interested</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-700 mb-0.5">Discussion Notes *</label>
                            <textarea name="notes" required rows="2" placeholder="Summary of phone call or discussion..." class="w-full border border-slate-300 rounded px-2 py-1 text-xs"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-2 items-end">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-700 mb-0.5">Next Follow-up Date</label>
                                <input type="date" name="next_followup_date" class="w-full border border-slate-300 rounded px-2 py-1 text-xs">
                            </div>
                            <div class="text-right">
                                <button type="submit" class="w-full px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                                    Save Call Log
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="p-3 border-t border-slate-200 bg-slate-50 flex justify-end">
                <button type="button" @click="viewLogsModal = false" class="px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded text-xs font-medium">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- RECORD NEW LEAD MODAL -->
    <div x-show="newLeadModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="newLeadModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Record New Student Enquiry</h3>
                <button @click="newLeadModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>
            
            <form method="POST" action="{{ route('crm.leads.store') }}" class="p-4 space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Full Name *</label>
                        <input type="text" name="full_name" required placeholder="e.g. Ramesh Chandra" class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Phone Number *</label>
                        <input type="text" name="phone" required placeholder="+91 98765 00000" class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="email" placeholder="name@example.com" class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">City / Area</label>
                        <input type="text" name="city" placeholder="Bengaluru" class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Course Interested In</label>
                        <select name="course_id" class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (₹{{ number_format($c->default_fee) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Lead Source *</label>
                        <select name="source" required class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="walkin">Walk-in Inquiry</option>
                            <option value="phone">Direct Phone Call</option>
                            <option value="website">Website Online Form</option>
                            <option value="referral">Student Referral</option>
                            <option value="social_media">Social Media (Instagram/FB)</option>
                            <option value="justdial">Justdial / Google Maps</option>
                            <option value="other">Other Source</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Initial Status *</label>
                        <select name="status" required class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="new">New</option>
                            <option value="contacted">Contacted</option>
                            <option value="interested" selected>Interested</option>
                            <option value="trial_booked">Trial Booked</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Next Follow-up Date</label>
                        <input type="date" name="follow_up_date" value="{{ now()->addDay()->toDateString() }}" class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Inquiry Requirements / Notes</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Needs morning 7 AM slot, prefers female instructor or automatic car..." class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs"></textarea>
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="newLeadModal = false" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold">Save Enquiry</button>
                </div>
            </form>
        </div>
    </div>

    <!-- STAND-ALONE LOG INTERACTION MODAL -->
    <div x-show="followupModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="followupModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">
                    Log Interaction: <span x-text="activeLead ? activeLead.full_name : ''"></span>
                </h3>
                <button @click="followupModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form :action="'/crm/leads/' + (activeLead ? activeLead.id : 0) + '/followup'" method="POST" class="p-4 space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Communication Channel *</label>
                        <select name="communication_channel" required class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="call">Phone Call</option>
                            <option value="whatsapp">WhatsApp Message</option>
                            <option value="in_person">In-Person Visit</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Outcome *</label>
                        <select name="outcome" required class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="connected">Connected & Discussed</option>
                            <option value="callback_requested">Callback Requested</option>
                            <option value="no_answer">No Answer / Busy</option>
                            <option value="enrolled">Agreed to Enroll</option>
                            <option value="not_interested">Not Interested</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Call Notes / Discussion Summary *</label>
                    <textarea name="notes" required rows="3" placeholder="Summary of discussion with the prospect..." class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs"></textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Next Follow-up Date</label>
                    <input type="date" name="next_followup_date" value="{{ now()->addDays(2)->toDateString() }}" class="w-full border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="followupModal = false" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold">Save Interaction</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
