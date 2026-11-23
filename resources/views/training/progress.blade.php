@extends('layouts.app')

@section('content')
<div class="space-y-4">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Student Training Progress Matrix</h1>
            <p class="text-xs text-slate-500">Real-time tracking of practical driving hours, theory attendance, and RTO test readiness.</p>
        </div>
        <a href="{{ route('schedules.create') }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
            + Schedule Lesson
        </a>
    </div>

    <!-- Progress Table -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3">Student</th>
                        <th class="py-2 px-3">Enrolled Course</th>
                        <th class="py-2 px-3">Practical Hours Done</th>
                        <th class="py-2 px-3">Theory Hours</th>
                        <th class="py-2 px-3">Mock Tests</th>
                        <th class="py-2 px-3">Completion %</th>
                        <th class="py-2 px-3">DL Test Readiness</th>
                        <th class="py-2 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($progressList as $p)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <a href="{{ route('students.show', $p->student_id) }}" class="font-bold text-blue-600 hover:underline">
                                    {{ $p->student?->full_name }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $p->student?->student_code }} &bull; {{ $p->student?->phone }}</div>
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-medium text-slate-900">{{ $p->student?->latestAdmission?->course?->name }}</div>
                                <div class="text-[10px] text-slate-400">{{ $p->admission?->package?->name ?? 'Standard' }}</div>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="font-bold text-slate-800">{{ $p->completed_practical_hours }}</span>
                                <span class="text-slate-400">/ {{ $p->required_practical_hours }} hrs</span>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="font-bold text-slate-800">{{ $p->completed_theory_hours }}</span>
                                <span class="text-slate-400">/ {{ $p->required_theory_hours }} hrs</span>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-1.5 py-0.2 rounded font-bold text-[10px] {{ $p->mock_tests_passed_count > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $p->mock_tests_passed_count }} Passed
                                </span>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ min(100, $p->overall_progress_percentage) }}%"></div>
                                    </div>
                                    <span class="font-bold text-slate-800">{{ round($p->overall_progress_percentage) }}%</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                @if($p->eligible_for_dl_test)
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        &#10003; Ready for DL Test
                                    </span>
                                @else
                                    <span class="text-[10px] text-amber-700 font-medium">Training Ongoing</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                <a href="{{ route('students.show', $p->student_id) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-[11px]">
                                    Profile &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-xs text-slate-400 italic">No student progress records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($progressList->hasPages())
            <div class="px-3 py-2 border-t border-slate-200 bg-slate-50">
                {{ $progressList->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
