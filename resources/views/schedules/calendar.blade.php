@extends('layouts.app')

@section('title', 'Lesson Master Calendar')
@section('page-title', 'Lesson Master Calendar')

@section('content')
<div class="space-y-3.5">
    
    <!-- TOP HEADER BAR & WEEK NAVIGATION -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-2 border-b border-slate-200 gap-2.5">
        <div>
            <h1 class="text-base font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Lesson Master Calendar
            </h1>
            <p class="text-xs text-slate-500">Visual weekly roster of practical driving sessions, vehicle fleet assignments, and instructor slots.</p>
        </div>

        <div class="flex items-center gap-2">
            @php
                $prevWeek = \Carbon\Carbon::parse($currentDate)->subWeek()->toDateString();
                $nextWeek = \Carbon\Carbon::parse($currentDate)->addWeek()->toDateString();
                $baseQuery = request()->except('date');
            @endphp
            
            <div class="flex items-center border border-slate-200 rounded bg-white shadow-xs text-xs">
                <a href="{{ route('schedules.calendar', array_merge($baseQuery, ['date' => $prevWeek])) }}" class="px-2.5 py-1 text-slate-600 hover:bg-slate-50 border-r border-slate-200 font-medium">
                    &larr; Prev Week
                </a>
                <a href="{{ route('schedules.calendar', array_merge($baseQuery, ['date' => now()->toDateString()])) }}" class="px-2.5 py-1 text-blue-700 font-bold hover:bg-slate-50">
                    Current Week
                </a>
                <a href="{{ route('schedules.calendar', array_merge($baseQuery, ['date' => $nextWeek])) }}" class="px-2.5 py-1 text-slate-600 hover:bg-slate-50 border-l border-slate-200 font-medium">
                    Next Week &rarr;
                </a>
            </div>

            <a href="{{ route('schedules.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Schedule Lesson</span>
            </a>
        </div>
    </div>

    <!-- TELEMETRY KPI METRICS BAR -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
        <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-blue-50 text-blue-600 flex items-center justify-center font-bold shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Week Sessions</div>
                <div class="text-base font-bold text-slate-800">{{ $totalSessions }} <span class="text-[11px] font-normal text-slate-500">lessons</span></div>
            </div>
        </div>

        <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Completed</div>
                <div class="text-base font-bold text-emerald-700">{{ $completedSessions }} <span class="text-[11px] font-normal text-slate-500">logged</span></div>
            </div>
        </div>

        <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Scheduled</div>
                <div class="text-base font-bold text-indigo-700">{{ $upcomingSessions }} <span class="text-[11px] font-normal text-slate-500">upcoming</span></div>
            </div>
        </div>

        <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-purple-50 text-purple-600 flex items-center justify-center font-bold shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Training Hours</div>
                <div class="text-base font-bold text-purple-700">{{ $totalHours }} <span class="text-[11px] font-normal text-slate-500">hrs</span></div>
            </div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('schedules.calendar') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-2.5 text-xs">
            <input type="hidden" name="date" value="{{ $currentDate }}">

            <!-- Branch filter -->
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Operating Branch</label>
                <select name="branch" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 bg-white">
                    <option value="all" {{ $branchFilter === 'all' ? 'selected' : '' }}>All Branches (Consolidated)</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchFilter == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Instructor filter -->
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Instructor</label>
                <select name="instructor" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 bg-white">
                    <option value="">All Instructors</option>
                    @foreach($instructors as $inst)
                        <option value="{{ $inst->id }}" {{ $instructorFilter == $inst->id ? 'selected' : '' }}>
                            {{ $inst->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status filter -->
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Lesson Status</label>
                <select name="status" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 bg-white">
                    <option value="">All Statuses</option>
                    <option value="scheduled" {{ $statusFilter === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="completed" {{ $statusFilter === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $statusFilter === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <!-- Jump to Date -->
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Jump to Week</label>
                <input type="date" name="date" value="{{ $currentDate }}" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
            </div>

            <!-- Action buttons -->
            <div class="flex items-end gap-1.5">
                <button type="submit" class="w-full py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded text-xs font-semibold transition-colors">
                    Filter Calendar
                </button>
                @if($branchFilter || $instructorFilter || $statusFilter || $currentDate !== now()->toDateString())
                    <a href="{{ route('schedules.calendar') }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded text-xs font-medium border border-slate-200" title="Reset Filters">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- 7-DAY GRID CALENDAR -->
    <div class="grid grid-cols-1 md:grid-cols-7 gap-2.5">
        @foreach($weekDays as $day)
            @php
                $daySchedules = $schedules->filter(fn($s) => $s->scheduled_date->toDateString() === $day['date']);
            @endphp
            <div class="bg-white border rounded-lg shadow-xs flex flex-col min-h-[520px] {{ $day['isToday'] ? 'border-blue-500 ring-2 ring-blue-400/40' : 'border-slate-200' }}">
                
                <!-- Day Column Header -->
                <div class="p-2 border-b text-center {{ $day['isToday'] ? 'bg-blue-50 border-blue-200 text-blue-900' : 'bg-slate-50 border-slate-200 text-slate-700' }}">
                    <div class="text-[10px] uppercase font-bold tracking-wider {{ $day['isToday'] ? 'text-blue-600' : 'text-slate-400' }}">
                        {{ $day['dayName'] }}
                    </div>
                    <div class="text-xs font-bold flex items-center justify-center gap-1">
                        <span>{{ $day['dayNumber'] }}</span>
                        @if($day['isToday'])
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                        @endif
                    </div>
                    <div class="text-[10px] font-medium {{ $daySchedules->count() > 0 ? ($day['isToday'] ? 'text-blue-700 font-semibold' : 'text-slate-600') : 'text-slate-400' }} mt-0.5">
                        {{ $daySchedules->count() }} {{ Str::plural('session', $daySchedules->count()) }}
                    </div>
                </div>

                <!-- Session Cards in Day Column -->
                <div class="p-1.5 flex-1 space-y-1.5 overflow-y-auto max-h-[620px]">
                    @forelse($daySchedules as $sch)
                        @php
                            $isCompleted = ($sch->status === 'completed');
                            $isCancelled = ($sch->status === 'cancelled');
                            $cardBg = $isCompleted 
                                ? 'bg-emerald-50/60 border-emerald-200 hover:bg-emerald-50' 
                                : ($isCancelled ? 'bg-rose-50/60 border-rose-200' : 'bg-blue-50/40 border-blue-200 hover:bg-blue-50/70');
                            $badgeClass = $isCompleted 
                                ? 'bg-emerald-100 text-emerald-800' 
                                : ($isCancelled ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800');
                        @endphp

                        <div class="p-2 rounded-md border text-xs {{ $cardBg }} shadow-2xs hover:shadow-xs transition-all">
                            <!-- Time & Status -->
                            <div class="flex items-center justify-between font-bold text-slate-900 text-[11px]">
                                <span>{{ \Carbon\Carbon::parse($sch->start_time)->format('h:i A') }}</span>
                                <span class="px-1.5 py-0.2 rounded text-[8px] font-bold uppercase {{ $badgeClass }}">
                                    {{ $sch->status }}
                                </span>
                            </div>

                            <!-- Student Link -->
                            <a href="{{ route('students.show', $sch->student_id) }}" class="font-bold text-blue-700 hover:underline block truncate mt-1 text-[11px]" title="{{ $sch->student?->full_name }}">
                                {{ $sch->student?->full_name }}
                            </a>

                            <!-- Topic -->
                            @if($sch->topic)
                                <div class="text-[10px] text-slate-600 line-clamp-2 mt-0.5 leading-snug" title="{{ $sch->topic }}">
                                    {{ $sch->topic }}
                                </div>
                            @endif

                            <!-- Instructor -->
                            <div class="text-[10px] text-slate-600 mt-1 truncate flex items-center gap-1">
                                <span class="text-slate-400">Inst:</span>
                                <span class="font-medium text-slate-800">{{ $sch->instructor?->name }}</span>
                            </div>

                            <!-- Vehicle & Branch -->
                            <div class="flex items-center justify-between gap-1 mt-1 pt-1 border-t border-slate-200/50">
                                @if($sch->vehicle)
                                    <span class="text-[9px] font-mono text-slate-600 bg-white/90 px-1 py-0.5 rounded border border-slate-200 truncate" title="{{ $sch->vehicle->model_name }}">
                                        {{ $sch->vehicle->registration_number }}
                                    </span>
                                @endif

                                @if($sch->branch)
                                    <span class="text-[8px] font-semibold text-slate-500 bg-slate-100 px-1 py-0.5 rounded truncate" title="{{ $sch->branch->name }}">
                                        {{ $sch->branch->code ?? substr($sch->branch->name, 0, 8) }}
                                    </span>
                                @endif
                            </div>

                            <!-- Duration & Action Link -->
                            <div class="mt-1 flex items-center justify-between text-[10px]">
                                <span class="text-slate-400">{{ $sch->duration_minutes }}m</span>
                                <a href="{{ route('schedules.complete-form', $sch->id) }}" class="text-blue-600 font-semibold hover:underline">
                                    {{ $sch->status === 'completed' ? 'Log Details' : 'Attendance' }} &rarr;
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-[10px] text-slate-400 italic">
                            No bookings
                        </div>
                    @endforelse
                </div>

            </div>
        @endforeach
    </div>

    <!-- CALENDAR LEGEND -->
    <div class="flex flex-wrap items-center justify-between gap-2 p-2.5 bg-white rounded-lg border border-slate-200 text-xs">
        <div class="flex items-center gap-4 text-[11px] text-slate-600">
            <span class="font-semibold text-slate-700">Status Legend:</span>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                <span>Scheduled Session</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span>Completed / Logged</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <span>Cancelled / Rescheduled</span>
            </div>
        </div>
        <div class="text-[11px] text-slate-500">
            Click on any session's <span class="font-semibold text-blue-600">Attendance</span> link to record odometer km, student rating, and instructor notes.
        </div>
    </div>

</div>
@endsection
