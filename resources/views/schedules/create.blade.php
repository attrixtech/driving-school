@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    
    <!-- Header -->
    <div class="flex items-center justify-between pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Schedule Driving Lesson</h1>
            <p class="text-xs text-slate-500">Allocate training slot with conflict prevention across instructors and vehicles.</p>
        </div>
        <a href="{{ route('schedules.index') }}" class="text-xs text-slate-600 hover:text-slate-900">&larr; Back to Schedules</a>
    </div>

    <!-- Smart Scheduler Card -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4 space-y-4">
        
        <div class="p-2.5 bg-blue-50/70 border border-blue-200 rounded text-xs text-blue-900 flex items-start gap-2">
            <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <strong>Intelligent Conflict Engine Active:</strong> The system automatically verifies that the instructor, student, and dual-control vehicle are completely free during the chosen time window.
            </div>
        </div>

        <form method="POST" action="{{ route('schedules.store') }}" class="space-y-3 text-xs">
            @csrf

            <!-- Operating Branch -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Operating Branch *</label>
                <select name="branch_id" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Student Selection -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Select Student *</label>
                <select name="student_id" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    <option value="">-- Choose Student --</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ ($student?->id == $s->id) ? 'selected' : '' }}>
                            {{ $s->full_name }} ({{ $s->student_code }}) - {{ $s->latestAdmission?->course?->name ?? 'Student' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Instructor Selection -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Select Certified Instructor *</label>
                <select name="instructor_id" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    <option value="">-- Choose Instructor --</option>
                    @foreach($instructors as $inst)
                        <option value="{{ $inst->id }}" {{ ($admission?->instructor_id == $inst->id) ? 'selected' : '' }}>
                            {{ $inst->name }} ({{ implode(', ', $inst->vehicle_types ?? []) }}) - Rating: {{ $inst->rating }}★
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Vehicle Selection -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Select Training Vehicle</label>
                <select name="vehicle_id" class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    <option value="">-- No vehicle (e.g. Theory class / Simulator) --</option>
                    @foreach($vehicles as $veh)
                        <option value="{{ $veh->id }}" {{ $loop->first ? 'selected' : '' }}>
                            {{ $veh->registration_number }} - {{ $veh->make }} {{ $veh->model }} ({{ ucfirst($veh->transmission) }}, Dual Control: {{ ucfirst($veh->dual_control_fitted) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Lesson Type & Topic -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Lesson Type *</label>
                    <select name="lesson_type" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        <option value="practical">Practical Driving Lesson</option>
                        <option value="theory">Theory & Road Signs Class</option>
                        <option value="simulator">Driving Simulator Training</option>
                        <option value="assessment">Practical Skill Assessment</option>
                        <option value="mock_test">RTO Mock Test Track Session</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Curriculum Topic / Focus</label>
                    <input type="text" name="topic" value="Practical Road Driving & Gear Transitions" placeholder="e.g. Parallel parking, clutch control..." class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
            </div>

            <!-- Date & Time Picker -->
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Scheduled Date *</label>
                    <input type="date" name="scheduled_date" value="{{ now()->toDateString() }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Start Time *</label>
                    <input type="time" name="start_time" value="09:00" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">End Time *</label>
                    <input type="time" name="end_time" value="10:00" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
            </div>

            <!-- Instructor Notes -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Special Notes / Route Instructions</label>
                <textarea name="notes" rows="2" placeholder="e.g. Pickup at Indiranagar Metro Station or ground maneuvers..." class="w-full form-textarea border border-slate-300 rounded px-2.5 py-1.5 text-xs"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                <a href="{{ route('schedules.index') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs font-semibold">Cancel</a>
                <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-bold shadow-xs">
                    Check Conflicts & Confirm Booking &rarr;
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
