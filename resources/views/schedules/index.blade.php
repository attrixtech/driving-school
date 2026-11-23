@extends('layouts.app')

@section('content')
<div class="space-y-4">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Practical Lessons & Sessions</h1>
            <p class="text-xs text-slate-500">Intelligent scheduling preventing double bookings of instructors, vehicles, and students.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('schedules.calendar') }}" class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded text-xs font-semibold shadow-xs">
                Weekly Calendar &rarr;
            </a>
            <a href="{{ route('schedules.create') }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Schedule Lesson</span>
            </a>
        </div>
    </div>

    <!-- Date & Status Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-md p-3 shadow-xs">
        <form method="GET" action="{{ route('schedules.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <label class="font-semibold text-slate-700 whitespace-nowrap">Filter Date:</label>
                <input type="date" name="date" value="{{ $date }}" class="form-input border border-slate-300 rounded px-2.5 py-1 text-xs">
                
                <select name="status" class="form-select border border-slate-300 rounded px-2.5 py-1 text-xs">
                    <option value="">All Statuses</option>
                    <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="missed" {{ $status === 'missed' ? 'selected' : '' }}>Missed</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                <button type="submit" class="px-3 py-1 bg-slate-800 hover:bg-slate-900 text-white rounded font-medium">Apply</button>
                <a href="{{ route('schedules.index') }}?date={{ now()->toDateString() }}" class="px-2 py-1 text-blue-600 hover:underline">Today</a>
            </div>

            <div class="text-[11px] text-slate-500">
                Showing slots for <strong>{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</strong>
            </div>
        </form>
    </div>

    <!-- Schedule Data Table -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3">Slot Time</th>
                        <th class="py-2 px-3">Student</th>
                        <th class="py-2 px-3">Instructor</th>
                        <th class="py-2 px-3">Vehicle</th>
                        <th class="py-2 px-3">Skill / Topic</th>
                        <th class="py-2 px-3">Attendance & Odometer</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schedules as $sch)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3 font-semibold text-slate-900 whitespace-nowrap">
                                <div>{{ \Carbon\Carbon::parse($sch->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($sch->end_time)->format('h:i A') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $sch->duration_minutes }} minutes</div>
                            </td>
                            <td class="py-2.5 px-3">
                                <a href="{{ route('students.show', $sch->student_id) }}" class="font-bold text-blue-600 hover:underline">
                                    {{ $sch->student?->full_name }}
                                </a>
                                <div class="text-[10px] text-slate-400">{{ $sch->student?->phone }}</div>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $sch->instructor?->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $sch->instructor?->instructor_code }}</div>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                @if($sch->vehicle)
                                    <span class="px-1.5 py-0.5 rounded font-mono font-bold text-[11px] bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $sch->vehicle->registration_number }}
                                    </span>
                                    <div class="text-[10px] text-slate-400">{{ $sch->vehicle->model }}</div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Classroom / Simulator</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 max-w-[160px] truncate" title="{{ $sch->topic }}">
                                <div class="font-medium text-slate-800">{{ $sch->topic ?? 'Driving session' }}</div>
                                <div class="text-[10px] text-slate-400 capitalize">{{ $sch->lesson_type }}</div>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap text-[11px]">
                                @if($sch->attendance)
                                    <div class="font-medium text-emerald-700">&#10003; Present ({{ $sch->attendance->kilometers_driven }} km)</div>
                                    @if($sch->attendance->student_rating)
                                        <div class="text-[10px] text-amber-500 font-bold">&#9733; {{ $sch->attendance->student_rating }}/5 stars</div>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Pending attendance</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase {{ $sch->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($sch->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $sch->status }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                @if($sch->status === 'scheduled')
                                    <a href="{{ route('schedules.complete-form', $sch->id) }}" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-semibold">
                                        Record Attendance
                                    </a>
                                @else
                                    <a href="{{ route('schedules.complete-form', $sch->id) }}" class="text-slate-500 hover:text-slate-800 text-[11px]">
                                        View Log &rarr;
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-xs text-slate-400 italic">
                                No lessons scheduled for this date. Click "+ Schedule Lesson" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($schedules->hasPages())
            <div class="px-3 py-2 border-t border-slate-200 bg-slate-50">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
