@extends('layouts.app')

@section('content')
<div class="space-y-4">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Student Directory</h1>
            <p class="text-xs text-slate-500">Monitor all registered students, their training hours, licensing milestones, and fee balances.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admissions.create') }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                <span>+ New Student Admission</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white border border-slate-200 rounded-md p-3 shadow-xs">
        <form method="GET" action="{{ route('students.index') }}" class="flex flex-col sm:flex-row items-center gap-2.5">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, student code, phone, or LL number..." class="w-full form-input pl-8 border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <select name="status" class="form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    <option value="">All Statuses</option>
                    <option value="enrolled" {{ $status === 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                    <option value="in_training" {{ $status === 'in_training' ? 'selected' : '' }}>In Training</option>
                    <option value="training_completed" {{ $status === 'training_completed' ? 'selected' : '' }}>Training Completed</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Course Completed</option>
                </select>

                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded text-xs font-medium">Filter</button>
                @if($search || $status)
                    <a href="{{ route('students.index') }}" class="px-2.5 py-1.5 text-slate-500 hover:text-slate-700 text-xs">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Students Table -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3">Student Code</th>
                        <th class="py-2 px-3">Student Name</th>
                        <th class="py-2 px-3">Contact</th>
                        <th class="py-2 px-3">Branch</th>
                        <th class="py-2 px-3">Active Course</th>
                        <th class="py-2 px-3">Training Progress</th>
                        <th class="py-2 px-3">LL / DL Status</th>
                        <th class="py-2 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                        @php
                            $prog = $student->progress;
                            $pct = $prog ? $prog->overall_progress_percentage : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3 font-mono font-semibold text-slate-800 whitespace-nowrap">
                                {{ $student->student_code }}
                            </td>
                            <td class="py-2.5 px-3">
                                <a href="{{ route('students.show', $student->id) }}" class="font-bold text-blue-600 hover:underline">
                                    {{ $student->full_name }}
                                </a>
                                @if($student->gender)
                                    <span class="text-[10px] text-slate-400 capitalize">({{ $student->gender }})</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $student->phone }}</div>
                                <div class="text-[10px] text-slate-400">{{ $student->email ?? 'No email' }}</div>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap text-slate-600">
                                {{ $student->branch?->name }}
                            </td>
                            <td class="py-2.5 px-3">
                                @if($student->latestAdmission)
                                    <div class="font-medium text-slate-800">{{ $student->latestAdmission->course?->name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ $student->latestAdmission->admission_number }}</div>
                                @else
                                    <span class="text-slate-400 italic">No admission</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ min(100, $pct) }}%"></div>
                                    </div>
                                    <span class="font-bold text-[11px] text-slate-800">{{ round($pct) }}%</span>
                                </div>
                                @if($prog)
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $prog->completed_practical_hours }} / {{ $prog->required_practical_hours }} hrs completed
                                    </div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                @if($student->learner_license_number)
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        LL: {{ $student->learner_license_number }}
                                    </span>
                                    @if($student->isLearnerLicenseExpiringSoon(20))
                                        <span class="block text-[9px] text-rose-600 font-bold mt-0.5">Expiring Soon!</span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ str_replace('_', ' ', $student->dl_status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('schedules.create') }}?student_id={{ $student->id }}" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-medium border border-slate-200">
                                        + Lesson
                                    </a>
                                    <a href="{{ route('students.show', $student->id) }}" class="px-2 py-0.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-[11px] font-semibold">
                                        Profile &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-xs text-slate-400 italic">
                                No students found. <a href="{{ route('admissions.create') }}" class="text-blue-600 font-semibold underline">Enroll a new student</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="px-3 py-2 border-t border-slate-200 bg-slate-50">
                {{ $students->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
