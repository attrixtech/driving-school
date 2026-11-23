@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ issueModal: false }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Driver Training Certificates</h1>
            <p class="text-xs text-slate-500">Official motorized training completion certificates with verifiable security codes and QR integration.</p>
        </div>
        <button @click="issueModal = true" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ Issue Certificate</span>
        </button>
    </div>

    <!-- Certificates Table -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3">Certificate No.</th>
                        <th class="py-2 px-3">Student Name</th>
                        <th class="py-2 px-3">Course / Class</th>
                        <th class="py-2 px-3">Issue Date</th>
                        <th class="py-2 px-3">Practical & Theory Hours</th>
                        <th class="py-2 px-3">Grade</th>
                        <th class="py-2 px-3">Verification Code</th>
                        <th class="py-2 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($certificates as $cert)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $cert->certificate_number }}
                            </td>
                            <td class="py-2.5 px-3">
                                <a href="{{ route('students.show', $cert->student_id) }}" class="font-bold text-blue-600 hover:underline">
                                    {{ $cert->student?->full_name }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $cert->student?->student_code }}</div>
                            </td>
                            <td class="py-2.5 px-3">
                                <div class="font-medium text-slate-800">{{ $cert->course_name }}</div>
                                <span class="px-1 py-0.2 rounded text-[9px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $cert->vehicle_category_code }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap text-slate-600">
                                {{ $cert->issue_date->format('d M Y') }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap font-medium text-slate-800">
                                {{ $cert->practical_hours_completed }} hrs Practical &bull; {{ $cert->theory_hours_completed }} hrs Theory
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap font-bold text-emerald-700">
                                {{ $cert->final_grade }}%
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap font-mono text-[10px] text-slate-500">
                                {{ substr($cert->verification_code, 0, 10) }}...
                            </td>
                            <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                <a href="{{ route('certificates.show', $cert->id) }}" target="_blank" class="px-2 py-0.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-[11px] font-semibold">
                                    View & Print &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-xs text-slate-400 italic">No certificates issued yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($certificates->hasPages())
            <div class="px-3 py-2 border-t border-slate-200 bg-slate-50">
                {{ $certificates->links() }}
            </div>
        @endif
    </div>

    <!-- ISSUE CERTIFICATE MODAL -->
    <div x-show="issueModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="issueModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Issue Driver Training Certificate</h3>
                <button @click="issueModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('certificates.issue') }}" class="p-4 space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Select Completed Student *</label>
                    <select name="student_id" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        <option value="">-- Choose Eligible Student --</option>
                        @foreach($eligibleStudents as $es)
                            <option value="{{ $es->id }}">
                                {{ $es->full_name }} ({{ $es->student_code }}) - {{ $es->latestAdmission?->course?->name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Only students with completed practical requirements are shown.</span>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Final Performance Assessment Grade (%) *</label>
                    <input type="number" step="0.01" name="grade" value="92.50" min="50" max="100" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs font-bold text-slate-900">
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="issueModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold">Generate Certificate</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
