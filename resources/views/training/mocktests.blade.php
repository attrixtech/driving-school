@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ newTestModal: false }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">RTO Mock Tests & Assessments</h1>
            <p class="text-xs text-slate-500">Practice tests on road signs, mandatory regulations, and vehicle hand controls for learner license holders.</p>
        </div>
        <button @click="newTestModal = true" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Record Mock Test Score</span>
        </button>
    </div>

    <!-- Mock Tests Table -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3">Date</th>
                        <th class="py-2 px-3">Student Name</th>
                        <th class="py-2 px-3">Test Topic</th>
                        <th class="py-2 px-3">Category</th>
                        <th class="py-2 px-3">Score</th>
                        <th class="py-2 px-3">Percentage</th>
                        <th class="py-2 px-3">Result</th>
                        <th class="py-2 px-3">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($mockTests as $test)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3 whitespace-nowrap font-medium text-slate-800">
                                {{ $test->created_at->format('d M Y') }}
                            </td>
                            <td class="py-2.5 px-3">
                                <a href="{{ route('students.show', $test->student_id) }}" class="font-bold text-blue-600 hover:underline">
                                    {{ $test->student?->full_name }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $test->student?->student_code }}</div>
                            </td>
                            <td class="py-2.5 px-3 font-medium text-slate-900">
                                {{ $test->test_title }}
                            </td>
                            <td class="py-2.5 px-3 capitalize text-slate-600">
                                {{ str_replace('_', ' ', $test->category) }}
                            </td>
                            <td class="py-2.5 px-3 font-bold text-slate-800">
                                {{ $test->correct_answers }} / {{ $test->total_questions }}
                            </td>
                            <td class="py-2.5 px-3 font-bold text-slate-800">
                                {{ $test->score_percentage }}%
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase {{ $test->is_passed ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $test->is_passed ? 'PASSED' : 'FAILED' }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-500 text-[11px]">
                                {{ $test->remarks }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-xs text-slate-400 italic">No mock test results recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mockTests->hasPages())
            <div class="px-3 py-2 border-t border-slate-200 bg-slate-50">
                {{ $mockTests->links() }}
            </div>
        @endif
    </div>

    <!-- RECORD MOCK TEST MODAL -->
    <div x-show="newTestModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="newTestModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Record Student Mock Test Score</h3>
                <button @click="newTestModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('training.mocktests.store') }}" class="p-4 space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Select Student *</label>
                    <select name="student_id" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        @foreach($students as $s)
                            <option value="{{ $s->id }}" {{ ($studentId == $s->id) ? 'selected' : '' }}>
                                {{ $s->full_name }} ({{ $s->student_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Test Title *</label>
                    <input type="text" name="test_title" value="RTO Official Pattern Mock Test 1" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Category *</label>
                    <select name="category" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        <option value="comprehensive_rto">Comprehensive RTO Test</option>
                        <option value="road_signs">Road Signs & Signals</option>
                        <option value="traffic_regulations">Traffic Rules & Penalties</option>
                        <option value="vehicle_controls">Vehicle Controls & Maintenance</option>
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Total Qs *</label>
                        <input type="number" name="total_questions" value="20" required class="w-full form-input border border-slate-300 rounded px-2 py-1 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Score *</label>
                        <input type="number" name="correct_answers" value="16" required class="w-full form-input border border-slate-300 rounded px-2 py-1 text-xs font-bold text-blue-700">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Pass Mark *</label>
                        <input type="number" name="passing_score" value="12" required class="w-full form-input border border-slate-300 rounded px-2 py-1 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Evaluator Remarks</label>
                    <input type="text" name="remarks" placeholder="Well prepared, excellent road sign awareness..." class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="newTestModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold">Save Result</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
