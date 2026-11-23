@extends('layouts.app')

@section('content')
<div class="space-y-4">
    
    <!-- Header with Weekly Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Lesson Master Calendar</h1>
            <p class="text-xs text-slate-500">Visual weekly roster of practical sessions, vehicle assignments, and trainer slots.</p>
        </div>

        <div class="flex items-center gap-2">
            @php
                $prevWeek = \Carbon\Carbon::parse($currentDate)->subWeek()->toDateString();
                $nextWeek = \Carbon\Carbon::parse($currentDate)->addWeek()->toDateString();
            @endphp
            <div class="flex items-center border border-slate-200 rounded bg-white shadow-xs text-xs">
                <a href="{{ route('schedules.calendar', ['date' => $prevWeek]) }}" class="px-2.5 py-1 text-slate-600 hover:bg-slate-50 border-r border-slate-200">&larr; Prev</a>
                <a href="{{ route('schedules.calendar', ['date' => now()->toDateString()]) }}" class="px-2.5 py-1 text-slate-800 font-bold hover:bg-slate-50">Today</a>
                <a href="{{ route('schedules.calendar', ['date' => $nextWeek]) }}" class="px-2.5 py-1 text-slate-600 hover:bg-slate-50 border-l border-slate-200">Next &rarr;</a>
            </div>

            <a href="{{ route('schedules.create') }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                + Schedule
            </a>
        </div>
    </div>

    <!-- 7-Day Grid Calendar -->
    <div class="grid grid-cols-1 md:grid-cols-7 gap-2.5">
        @foreach($weekDays as $day)
            @php
                $daySchedules = $schedules->filter(fn($s) => $s->scheduled_date->toDateString() === $day['date']);
            @endphp
            <div class="bg-white border rounded-md shadow-xs flex flex-col min-h-[450px] {{ $day['isToday'] ? 'border-blue-500 ring-1 ring-blue-400' : 'border-slate-200' }}">
                
                <!-- Day Header -->
                <div class="p-2 border-b text-center {{ $day['isToday'] ? 'bg-blue-50/70 border-blue-200 text-blue-900' : 'bg-slate-50/70 border-slate-200 text-slate-700' }}">
                    <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400">{{ $day['dayName'] }}</div>
                    <div class="text-xs font-bold">{{ $day['dayNumber'] }}</div>
                    <div class="text-[9px] text-slate-400 mt-0.5">{{ $daySchedules->count() }} sessions</div>
                </div>

                <!-- Session Cards in Day Column -->
                <div class="p-1.5 flex-1 space-y-1.5 overflow-y-auto max-h-[500px]">
                    @forelse($daySchedules as $sch)
                        <div class="p-2 rounded border text-xs {{ $sch->status === 'completed' ? 'bg-emerald-50/50 border-emerald-200' : 'bg-blue-50/40 border-blue-200' }} hover:shadow-xs transition-shadow">
                            <div class="flex items-center justify-between font-bold text-slate-900 text-[11px]">
                                <span>{{ \Carbon\Carbon::parse($sch->start_time)->format('h:i A') }}</span>
                                <span class="px-1 py-0.2 rounded text-[8px] font-bold uppercase {{ $sch->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $sch->status }}
                                </span>
                            </div>

                            <a href="{{ route('students.show', $sch->student_id) }}" class="font-bold text-blue-700 hover:underline block truncate mt-1">
                                {{ $sch->student?->full_name }}
                            </a>

                            <div class="text-[10px] text-slate-600 mt-0.5 truncate">
                                Inst: <strong>{{ $sch->instructor?->name }}</strong>
                            </div>

                            @if($sch->vehicle)
                                <div class="text-[9px] font-mono text-slate-500 mt-1 bg-white/80 px-1 py-0.5 rounded border border-slate-200/60 inline-block">
                                    {{ $sch->vehicle->registration_number }}
                                </div>
                            @endif

                            <div class="mt-1 pt-1 border-t border-slate-200/50 flex items-center justify-between text-[10px]">
                                <span class="text-slate-400">{{ $sch->duration_minutes }}m</span>
                                <a href="{{ route('schedules.complete-form', $sch->id) }}" class="text-blue-600 font-semibold hover:underline">
                                    {{ $sch->status === 'completed' ? 'Log' : 'Attendance' }}
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-[10px] text-slate-400 italic">No bookings</div>
                    @endforelse
                </div>

            </div>
        @endforeach
    </div>

</div>
@endsection
