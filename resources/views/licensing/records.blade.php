@extends('layouts.app')

@section('content')
<div class="space-y-4">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Learner & Driving License (RTO) Records</h1>
            <p class="text-xs text-slate-500">Monitor government RTO document validity, learner license renewals, and driving test appointments.</p>
        </div>
    </div>

    <!-- Status filter bar -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
        <a href="{{ route('licensing.records') }}" class="px-2.5 py-1 rounded font-medium transition-colors {{ empty($dlStatus) ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            All Students
        </a>
        <a href="{{ route('licensing.records', ['dl_status' => 'll_issued']) }}" class="px-2.5 py-1 rounded font-medium transition-colors {{ $dlStatus === 'll_issued' ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            LL Issued (Under Training)
        </a>
        <a href="{{ route('licensing.records', ['dl_status' => 'dl_test_scheduled']) }}" class="px-2.5 py-1 rounded font-medium transition-colors {{ $dlStatus === 'dl_test_scheduled' ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            DL Test Scheduled
        </a>
        <a href="{{ route('licensing.records', ['dl_status' => 'dl_issued']) }}" class="px-2.5 py-1 rounded font-medium transition-colors {{ $dlStatus === 'dl_issued' ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Permanent DL Issued
        </a>
    </div>

    <!-- Records Table -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3">Student</th>
                        <th class="py-2 px-3">Branch</th>
                        <th class="py-2 px-3">Learner License No.</th>
                        <th class="py-2 px-3">LL Validity & Expiry</th>
                        <th class="py-2 px-3">RTO Test Milestone</th>
                        <th class="py-2 px-3">DL Appointment Date</th>
                        <th class="py-2 px-3">Permanent DL Number</th>
                        <th class="py-2 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $s)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2.5 px-3">
                                <a href="{{ route('students.show', $s->id) }}" class="font-bold text-blue-600 hover:underline">
                                    {{ $s->full_name }}
                                </a>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $s->student_code }} &bull; {{ $s->phone }}</div>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 whitespace-nowrap">
                                {{ $s->branch?->name }}
                            </td>
                            <td class="py-2.5 px-3 font-mono font-semibold text-slate-900 whitespace-nowrap">
                                {{ $s->learner_license_number ?? 'Not Applied' }}
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                @if($s->ll_expiry_date)
                                    @php $expSoon = $s->isLearnerLicenseExpiringSoon(20); @endphp
                                    <div class="{{ $expSoon ? 'text-rose-600 font-bold' : 'text-slate-800' }}">
                                        {{ $s->ll_expiry_date->format('d M Y') }}
                                        @if($expSoon)
                                            <span class="text-[9px] bg-rose-100 text-rose-800 px-1 py-0.2 rounded font-semibold ml-1">Expiring Soon!</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-slate-400">Issued: {{ $s->ll_issue_date ? $s->ll_issue_date->format('d M Y') : 'N/A' }}</div>
                                @else
                                    <span class="text-slate-400 italic">No LL dates</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase {{ $s->dl_status === 'dl_issued' ? 'bg-emerald-100 text-emerald-800' : ($s->dl_status === 'dl_test_scheduled' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ str_replace('_', ' ', $s->dl_status) }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 whitespace-nowrap">
                                @if($s->dl_test_date)
                                    <div class="font-bold text-purple-800">{{ $s->dl_test_date->format('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $s->dl_rto_office ?? 'Local RTO' }}</div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Not Scheduled</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $s->permanent_license_number ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                <a href="{{ route('students.show', $s->id) }}#license" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-medium border border-slate-200">
                                    Update RTO
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-xs text-slate-400 italic">No license records found.</td>
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
