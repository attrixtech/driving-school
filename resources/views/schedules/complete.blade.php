@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-4" 
     x-data="{
        startOdo: {{ $schedule->attendance?->start_odometer ?? ($schedule->vehicle?->odometer_reading ?? 0) }},
        endOdo: {{ $schedule->attendance?->end_odometer ?? (($schedule->vehicle?->odometer_reading ?? 0) + 15) }},
        get kmDriven() {
            return Math.max(0, this.endOdo - this.startOdo);
        }
     }">
    
    <!-- Header -->
    <div class="flex items-center justify-between pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Record Attendance & Lesson Completion</h1>
            <p class="text-xs text-slate-500">Log driving kilometers, student evaluation, and update training progress.</p>
        </div>
        <a href="{{ route('schedules.index') }}" class="text-xs text-slate-600 hover:text-slate-900">&larr; Back to Schedules</a>
    </div>

    <!-- Lesson Overview Card -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4 space-y-3 text-xs">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div>
                <span class="font-bold text-slate-900 text-sm">{{ $schedule->student?->full_name }}</span>
                <span class="text-slate-500 ml-1 font-mono">({{ $schedule->student?->student_code }})</span>
            </div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $schedule->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                {{ $schedule->status }}
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-slate-600">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Date</span>
                <strong class="text-slate-800">{{ $schedule->scheduled_date->format('d M Y') }}</strong>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Time Slot</span>
                <strong class="text-slate-800">{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</strong>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Instructor</span>
                <strong class="text-slate-800">{{ $schedule->instructor?->name }}</strong>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Vehicle</span>
                <strong class="text-slate-800 font-mono">{{ $schedule->vehicle?->registration_number ?? 'Classroom' }}</strong>
            </div>
        </div>
    </div>

    <!-- Attendance Form -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4 space-y-4 text-xs">
        <form method="POST" action="{{ route('schedules.complete', $schedule->id) }}" class="space-y-3">
            @csrf

            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Student Attendance Status *</label>
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-1.5">
                        <input type="radio" name="status" value="present" checked class="text-emerald-600">
                        <span class="font-medium text-emerald-700">Present & Completed</span>
                    </label>
                    <label class="flex items-center gap-1.5">
                        <input type="radio" name="status" value="absent" class="text-rose-600">
                        <span class="font-medium text-rose-700">Absent (No Show)</span>
                    </label>
                    <label class="flex items-center gap-1.5">
                        <input type="radio" name="status" value="late" class="text-amber-600">
                        <span class="font-medium text-amber-700">Late Arrival</span>
                    </label>
                </div>
            </div>

            <!-- Actual Timings -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Actual Start Time</label>
                    <input type="time" name="actual_start_time" value="{{ $schedule->attendance?->actual_start_time ?? substr($schedule->start_time, 0, 5) }}" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Actual End Time</label>
                    <input type="time" name="actual_end_time" value="{{ $schedule->attendance?->actual_end_time ?? substr($schedule->end_time, 0, 5) }}" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
            </div>

            <!-- Odometer Readings (If vehicle assigned) -->
            @if($schedule->vehicle_id)
            <div class="p-3 bg-slate-50 border border-slate-200 rounded space-y-2">
                <div class="font-bold text-slate-800 pb-1 border-b border-slate-200 flex justify-between">
                    <span>Vehicle Kilometers Driven Tracking</span>
                    <span class="text-blue-700 font-bold" x-text="kmDriven + ' km recorded'"></span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Start Odometer Reading *</label>
                        <input type="number" name="start_odometer" x-model.number="startOdo" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs font-mono font-semibold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">End Odometer Reading *</label>
                        <input type="number" name="end_odometer" x-model.number="endOdo" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs font-mono font-semibold">
                    </div>
                </div>
            </div>
            @endif

            <!-- Student Evaluation & Feedback -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Student Performance Rating (1-5 Stars)</label>
                    <select name="student_rating" class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        <option value="5" selected>5 Stars - Outstanding Control</option>
                        <option value="4">4 Stars - Very Good</option>
                        <option value="3">3 Stars - Satisfactory (Needs Practice)</option>
                        <option value="2">2 Stars - Weak (Clutch / Steering issues)</option>
                        <option value="1">1 Star - Needs Fundamental Training</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Student Feedback</label>
                    <input type="text" name="student_feedback" placeholder="Student felt confident, etc." class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
            </div>

            <!-- Instructor Notes -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Instructor Evaluation Remarks</label>
                <textarea name="instructor_notes" rows="2" placeholder="Specific maneuvers practiced (reverse parking, slope stop-and-start, roundabout discipline)..." class="w-full form-textarea border border-slate-300 rounded px-2.5 py-1.5 text-xs"></textarea>
            </div>

            <!-- Submit -->
            <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                <a href="{{ route('schedules.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs">Cancel</a>
                <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold shadow-xs">
                    &#10003; Mark Completed & Recalculate Progress
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
