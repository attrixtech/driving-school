@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-4" x-data="{ activeTab: 'training', docModal: false, licenseModal: false }">
    
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-bold text-sm flex items-center justify-center">
                {{ strtoupper(substr($student->first_name, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base font-bold text-slate-900 tracking-tight">{{ $student->full_name }}</h1>
                    <span class="px-1.5 py-0.2 rounded font-mono text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $student->student_code }}
                    </span>
                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase {{ $student->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ str_replace('_', ' ', $student->status) }}
                    </span>
                </div>
                <div class="text-xs text-slate-500 mt-0.5">
                    {{ $student->phone }} | {{ $student->email ?? 'No email' }} | Branch: {{ $student->branch?->name }}
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('schedules.create') }}?student_id={{ $student->id }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                + Schedule Lesson
            </a>
            @if($student->latestAdmission)
                <a href="{{ route('admissions.show', $student->latestAdmission->id) }}" class="px-3 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded text-xs font-semibold">
                    View Admission
                </a>
            @endif
        </div>
    </div>

    <!-- Student Training Progress Gauge Card -->
    @php
        $prog = $student->progress;
        $pct = $prog ? $prog->overall_progress_percentage : 0;
    @endphp
    <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-2">
            <div>
                <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider text-[11px]">Training Progress & Readiness</h2>
                <div class="text-xs text-slate-500">
                    Course: <strong>{{ $student->latestAdmission?->course?->name ?? 'Standard Driving' }}</strong>
                </div>
            </div>
            <div>
                @if($prog && $prog->eligible_for_dl_test)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        &#10003; Ready for RTO Driving License Test
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 text-amber-800 border border-amber-200">
                        In Progress ({{ round($pct) }}% Complete)
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mt-3">
            
            <!-- Overall Progress Bar -->
            <div class="sm:col-span-1 p-3 bg-slate-50 rounded border border-slate-200 flex flex-col justify-center text-center">
                <div class="text-2xl font-black text-blue-700">{{ round($pct) }}%</div>
                <div class="text-[10px] uppercase font-bold text-slate-400 mt-1">Course Completion</div>
                <div class="w-full bg-slate-200 rounded-full h-1.5 mt-2 overflow-hidden">
                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ min(100, $pct) }}%"></div>
                </div>
            </div>

            <!-- Practical Hours -->
            <div class="p-3 bg-slate-50 rounded border border-slate-200 text-xs">
                <div class="flex justify-between items-center text-slate-500 mb-1">
                    <span class="text-[10px] uppercase font-bold">Practical Hours</span>
                    <span class="font-bold text-slate-800">{{ $prog?->completed_practical_hours ?? 0 }} / {{ $prog?->required_practical_hours ?? 15 }} hrs</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                    @php $pracPct = $prog ? min(100, ($prog->completed_practical_hours / max(1, $prog->required_practical_hours)) * 100) : 0; @endphp
                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $pracPct }}%"></div>
                </div>
                <div class="text-[10px] text-slate-500 mt-2">1-on-1 dual-control car driving</div>
            </div>

            <!-- Theory Hours -->
            <div class="p-3 bg-slate-50 rounded border border-slate-200 text-xs">
                <div class="flex justify-between items-center text-slate-500 mb-1">
                    <span class="text-[10px] uppercase font-bold">Theory Classes</span>
                    <span class="font-bold text-slate-800">{{ $prog?->completed_theory_hours ?? 0 }} / {{ $prog?->required_theory_hours ?? 5 }} hrs</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                    @php $theoPct = $prog ? min(100, ($prog->completed_theory_hours / max(1, $prog->required_theory_hours)) * 100) : 0; @endphp
                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $theoPct }}%"></div>
                </div>
                <div class="text-[10px] text-slate-500 mt-2">Road signs & RTO rules</div>
            </div>

            <!-- Mock Test Passed -->
            <div class="p-3 bg-slate-50 rounded border border-slate-200 text-xs">
                <div class="flex justify-between items-center text-slate-500 mb-1">
                    <span class="text-[10px] uppercase font-bold">Mock Tests</span>
                    <span class="font-bold text-slate-800">{{ $prog?->mock_tests_passed_count ?? 0 }} Passed</span>
                </div>
                <div class="text-[10px] text-slate-500 mt-2">
                    @if(($prog?->mock_tests_passed_count ?? 0) >= 1)
                        <span class="text-emerald-700 font-semibold">&#10003; Minimum required met</span>
                    @else
                        <span class="text-amber-700 font-semibold">1 test required</span>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- TAB NAVIGATION -->
    <div class="flex items-center gap-1 border-b border-slate-200 text-xs font-medium">
        <button @click="activeTab = 'training'" :class="activeTab === 'training' ? 'border-b-2 border-blue-600 text-blue-700 font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-2">
            Practical Lessons ({{ $student->lessonSchedules->count() }})
        </button>
        <button @click="activeTab = 'documents'" :class="activeTab === 'documents' ? 'border-b-2 border-blue-600 text-blue-700 font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-2">
            Documents Vault ({{ $student->documents->count() }})
        </button>
        <button @click="activeTab = 'license'" :class="activeTab === 'license' ? 'border-b-2 border-blue-600 text-blue-700 font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-2">
            Licensing & RTO
        </button>
        <button @click="activeTab = 'finance'" :class="activeTab === 'finance' ? 'border-b-2 border-blue-600 text-blue-700 font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-2">
            Invoices & Payments
        </button>
        <button @click="activeTab = 'tests'" :class="activeTab === 'tests' ? 'border-b-2 border-blue-600 text-blue-700 font-bold' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-2">
            Mock Tests ({{ $student->mockTests->count() }})
        </button>
    </div>

    <!-- TAB 1: PRACTICAL LESSONS & ATTENDANCE LOG -->
    <div x-show="activeTab === 'training'" class="space-y-3">
        <div class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
            <div class="flex items-center justify-between px-3.5 py-2.5 border-b border-slate-200">
                <h3 class="text-xs font-bold text-slate-800">Lesson History & Driving Log</h3>
                <a href="{{ route('schedules.create') }}?student_id={{ $student->id }}" class="text-[11px] font-semibold text-blue-600 hover:text-blue-800">+ Schedule New Lesson</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                            <th class="py-2 px-3">Date & Slot</th>
                            <th class="py-2 px-3">Topic / Skill</th>
                            <th class="py-2 px-3">Instructor</th>
                            <th class="py-2 px-3">Vehicle</th>
                            <th class="py-2 px-3">Km Driven</th>
                            <th class="py-2 px-3">Status</th>
                            <th class="py-2 px-3">Rating / Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($student->lessonSchedules as $sch)
                            <tr>
                                <td class="py-2 px-3 whitespace-nowrap">
                                    <div class="font-bold text-slate-900">{{ $sch->scheduled_date->format('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-400">
                                        {{ \Carbon\Carbon::parse($sch->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($sch->end_time)->format('h:i A') }}
                                    </div>
                                </td>
                                <td class="py-2 px-3 font-medium text-slate-800">
                                    {{ $sch->topic ?? 'Driving session' }}
                                </td>
                                <td class="py-2 px-3 whitespace-nowrap">{{ $sch->instructor?->name }}</td>
                                <td class="py-2 px-3 whitespace-nowrap font-mono text-[11px]">
                                    {{ $sch->vehicle?->registration_number ?? 'N/A' }}
                                </td>
                                <td class="py-2 px-3 whitespace-nowrap font-semibold">
                                    {{ $sch->attendance?->kilometers_driven ? $sch->attendance->kilometers_driven . ' km' : '-' }}
                                </td>
                                <td class="py-2 px-3 whitespace-nowrap">
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase {{ $sch->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $sch->status }}
                                    </span>
                                </td>
                                <td class="py-2 px-3 max-w-[200px] truncate text-slate-500 text-[11px]" title="{{ $sch->attendance?->instructor_notes }}">
                                    @if($sch->attendance?->student_rating)
                                        <span class="text-amber-500 font-bold">&#9733; {{ $sch->attendance->student_rating }}/5</span>
                                    @endif
                                    {{ $sch->attendance?->instructor_notes ?? $sch->notes ?? 'None' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-xs text-slate-400 italic">No lessons recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: DOCUMENTS VAULT -->
    <div x-show="activeTab === 'documents'" class="space-y-3">
        <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-800">Uploaded Identity & License Documents</h3>
                <button @click="docModal = true" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold">
                    + Upload Document
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-3">
                @forelse($student->documents as $doc)
                    <div class="p-3 border border-slate-200 rounded-md bg-slate-50/50 flex flex-col justify-between text-xs space-y-2">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 uppercase text-[10px] tracking-wider">{{ str_replace('_', ' ', $doc->document_type) }}</span>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase {{ $doc->verification_status === 'verified' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $doc->verification_status }}
                                </span>
                            </div>
                            <div class="text-[11px] font-mono text-slate-600 mt-1">{{ $doc->document_number ?? 'No ID number' }}</div>
                            <div class="text-[10px] text-slate-400 truncate mt-0.5">{{ $doc->original_filename }}</div>
                        </div>

                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
                            <form method="POST" action="{{ route('students.documents.verify', $doc->id) }}" class="inline">
                                @csrf
                                @if($doc->verification_status !== 'verified')
                                    <button type="submit" name="status" value="verified" class="text-[10px] text-emerald-700 font-semibold hover:underline">&#10003; Verify</button>
                                @else
                                    <span class="text-[10px] text-emerald-600 font-medium">Verified Staff</span>
                                @endif
                            </form>
                            <span class="text-[10px] text-slate-400">{{ $doc->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center py-6 text-xs text-slate-400 italic">No documents uploaded yet. Upload Aadhaar card or Learner License copy.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- TAB 3: LICENSING & RTO -->
    <div x-show="activeTab === 'license'" class="space-y-3">
        <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-800">Learner License & Driving License Lifecycle</h3>
                <button @click="licenseModal = true" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-900 text-white rounded text-xs font-semibold">
                    Edit License Details
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3 text-xs">
                
                <!-- Learner License Box -->
                <div class="p-3 border border-slate-200 rounded bg-slate-50/50 space-y-1.5">
                    <div class="font-bold text-slate-900 pb-1 border-b border-slate-200">Learner License (LL)</div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">LL Number:</span>
                        <span class="font-mono font-bold text-slate-800">{{ $student->learner_license_number ?? 'Not Recorded' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Issue Date:</span>
                        <span>{{ $student->ll_issue_date ? $student->ll_issue_date->format('d M Y') : 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Expiry Date:</span>
                        <span class="{{ $student->isLearnerLicenseExpiringSoon(20) ? 'text-rose-600 font-bold' : 'text-slate-800' }}">
                            {{ $student->ll_expiry_date ? $student->ll_expiry_date->format('d M Y') : 'N/A' }}
                        </span>
                    </div>
                </div>

                <!-- Permanent Driving License Box -->
                <div class="p-3 border border-slate-200 rounded bg-slate-50/50 space-y-1.5">
                    <div class="font-bold text-slate-900 pb-1 border-b border-slate-200">Permanent Driving License (DL)</div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Current Status:</span>
                        <span class="font-bold text-blue-700 uppercase text-[10px]">{{ str_replace('_', ' ', $student->dl_status) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">RTO Test Date:</span>
                        <span>{{ $student->dl_test_date ? $student->dl_test_date->format('d M Y') : 'Not Scheduled' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Permanent DL No:</span>
                        <span class="font-mono font-bold text-emerald-700">{{ $student->permanent_license_number ?? 'Awaiting Test' }}</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- TAB 4: FINANCE -->
    <div x-show="activeTab === 'finance'" class="space-y-3">
        <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4">
            <h3 class="text-xs font-bold text-slate-800 pb-2 border-b border-slate-100">Financial History & Transactions</h3>

            <div class="space-y-3 mt-3">
                @foreach($student->admissions as $adm)
                    <div class="p-3 border border-slate-200 rounded bg-slate-50/40 text-xs">
                        <div class="flex items-center justify-between pb-1.5 border-b border-slate-200">
                            <div>
                                <span class="font-bold text-slate-900">Admission #{{ $adm->admission_number }}</span>
                                <span class="text-slate-500 ml-1">({{ $adm->course?->name }})</span>
                            </div>
                            <span class="font-bold text-slate-800">Total: ₹{{ number_format($adm->total_amount, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2 text-[11px]">
                            <span>Paid: <strong class="text-emerald-700">₹{{ number_format($adm->paid_amount, 2) }}</strong></span>
                            <span>Balance Due: <strong class="{{ $adm->balance_amount > 0 ? 'text-rose-600' : 'text-slate-500' }}">₹{{ number_format($adm->balance_amount, 2) }}</strong></span>
                            @if($adm->balance_amount > 0)
                                <a href="{{ route('billing.payments') }}?collect=1&student_id={{ $student->id }}&admission_id={{ $adm->id }}" class="px-2 py-0.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[10px] font-semibold">
                                    + Collect Fee
                                </a>
                            @else
                                <span class="text-emerald-600 font-bold">&#10003; Clear</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- TAB 5: MOCK TESTS -->
    <div x-show="activeTab === 'tests'" class="space-y-3">
        <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-800">RTO Traffic Signs & Knowledge Mock Tests</h3>
                <a href="{{ route('training.mocktests') }}?student_id={{ $student->id }}" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold">
                    + Take Mock Test
                </a>
            </div>

            <div class="overflow-x-auto mt-3">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                            <th class="py-1.5 px-3">Date</th>
                            <th class="py-1.5 px-3">Test Title</th>
                            <th class="py-1.5 px-3">Score</th>
                            <th class="py-1.5 px-3">Percentage</th>
                            <th class="py-1.5 px-3">Result</th>
                            <th class="py-1.5 px-3">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($student->mockTests as $test)
                            <tr>
                                <td class="py-2 px-3">{{ $test->created_at->format('d M Y') }}</td>
                                <td class="py-2 px-3 font-medium text-slate-800">{{ $test->test_title }}</td>
                                <td class="py-2 px-3 font-semibold">{{ $test->correct_answers }} / {{ $test->total_questions }}</td>
                                <td class="py-2 px-3 font-bold">{{ $test->score_percentage }}%</td>
                                <td class="py-2 px-3">
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase {{ $test->is_passed ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $test->is_passed ? 'PASSED' : 'FAILED' }}
                                    </span>
                                </td>
                                <td class="py-2 px-3 text-slate-500">{{ $test->remarks }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-center text-xs text-slate-400 italic">No mock tests attempted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- DOCUMENT UPLOAD MODAL -->
    <div x-show="docModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="docModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Upload Student Document</h3>
                <button @click="docModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('students.documents.upload', $student->id) }}" enctype="multipart/form-data" class="p-4 space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Document Type *</label>
                    <select name="document_type" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        <option value="aadhaar">Aadhaar Card</option>
                        <option value="ll_copy">Learner License Copy</option>
                        <option value="medical_certificate">Medical Fitness Certificate (Form 1A)</option>
                        <option value="passport">Passport</option>
                        <option value="voter_id">Voter ID</option>
                        <option value="photo">Passport Size Photograph</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Document / ID Number</label>
                    <input type="text" name="document_number" placeholder="e.g. 12-digit Aadhaar / LL number" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Select File (PDF / Image) *</label>
                    <input type="file" name="document_file" required accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="docModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <!-- LICENSE EDIT MODAL -->
    <div x-show="licenseModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="licenseModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Update License & RTO Milestones</h3>
                <button @click="licenseModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('students.license.update', $student->id) }}" class="p-4 space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Learner License (LL) No.</label>
                    <input type="text" name="learner_license_number" value="{{ $student->learner_license_number }}" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs font-mono">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">LL Issue Date</label>
                        <input type="date" name="ll_issue_date" value="{{ $student->ll_issue_date?->toDateString() }}" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">LL Expiry Date</label>
                        <input type="date" name="ll_expiry_date" value="{{ $student->ll_expiry_date?->toDateString() }}" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">RTO Milestone Status *</label>
                    <select name="dl_status" class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        <option value="not_applied" {{ $student->dl_status === 'not_applied' ? 'selected' : '' }}>Not Applied</option>
                        <option value="ll_applied" {{ $student->dl_status === 'll_applied' ? 'selected' : '' }}>LL Application Submitted</option>
                        <option value="ll_issued" {{ $student->dl_status === 'll_issued' ? 'selected' : '' }}>LL Issued by RTO</option>
                        <option value="dl_applied" {{ $student->dl_status === 'dl_applied' ? 'selected' : '' }}>DL Test Application Filed</option>
                        <option value="dl_test_scheduled" {{ $student->dl_status === 'dl_test_scheduled' ? 'selected' : '' }}>DL Practical Test Scheduled</option>
                        <option value="dl_passed" {{ $student->dl_status === 'dl_passed' ? 'selected' : '' }}>DL Test Passed at RTO Track</option>
                        <option value="dl_issued" {{ $student->dl_status === 'dl_issued' ? 'selected' : '' }}>Permanent DL Issued</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">DL Practical Test Date</label>
                        <input type="date" name="dl_test_date" value="{{ $student->dl_test_date?->toDateString() }}" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">RTO Office</label>
                        <input type="text" name="dl_rto_office" value="{{ $student->dl_rto_office ?? 'UP-80 Agra RTO' }}" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Permanent DL Number (If issued)</label>
                    <input type="text" name="permanent_license_number" value="{{ $student->permanent_license_number }}" placeholder="UP-80-2026-XXXXXXX" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs font-mono">
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="licenseModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold">Update License</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
