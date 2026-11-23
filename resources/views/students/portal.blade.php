@extends('layouts.app')

@section('title', 'Student Training & Learning Portal')

@section('content')
<div class="space-y-4">
    <!-- Student Header Banner -->
    <div class="bg-gradient-to-r from-blue-700 to-indigo-800 rounded-lg p-3.5 text-white shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center font-bold text-lg text-white border border-white/30">
                    {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-base font-bold">{{ $student->full_name }}</h1>
                        <span class="text-[10px] font-mono bg-white/20 px-1.5 py-0.2 rounded">{{ $student->student_code }}</span>
                        <span class="text-[10px] font-bold px-2 py-0.2 rounded uppercase bg-emerald-500/30 border border-emerald-300 text-emerald-100">
                            {{ $student->status }}
                        </span>
                    </div>
                    <div class="text-xs text-blue-100 mt-0.5 flex flex-wrap items-center gap-3">
                        <span>Course: <strong>{{ $student->latestAdmission?->course?->name ?? 'Motor Driving Training' }}</strong></span>
                        <span>•</span>
                        <span>Package: <strong>{{ $student->latestAdmission?->package?->name ?? 'Standard Package' }}</strong></span>
                        <span>•</span>
                        <span>Branch: <strong>{{ $student->branch?->name }}</strong></span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($student->certificates->count() > 0)
                <a href="{{ route('certificates.show', $student->certificates->first()->id) }}" target="_blank" class="px-2.5 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded font-medium text-xs shadow-sm flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Download Form 5 Certificate</span>
                </a>
                @endif
                <a href="{{ route('students.show', $student->id) }}" class="px-2.5 py-1.5 bg-white/10 hover:bg-white/20 text-white rounded font-medium text-xs border border-white/20 flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>Full File Dossier</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Telemetry Mappings -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5">
        <div class="bg-white border border-slate-200 rounded p-2.5">
            <div class="text-[10px] uppercase font-bold text-slate-400">Practical Driving Hours</div>
            <div class="text-base font-bold text-blue-700 mt-0.5">
                {{ $student->progress?->practical_hours_completed ?? 0 }} <span class="text-xs text-slate-400 font-normal">/ {{ $student->progress?->practical_hours_required ?? 15 }} hrs</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1.5 overflow-hidden">
                @php $practPct = min(100, round((($student->progress?->practical_hours_completed ?? 0) / max(1, $student->progress?->practical_hours_required ?? 15)) * 100)); @endphp
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $practPct }}%"></div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded p-2.5">
            <div class="text-[10px] uppercase font-bold text-slate-400">Theory Classes Attended</div>
            <div class="text-base font-bold text-indigo-700 mt-0.5">
                {{ $student->progress?->theory_classes_attended ?? 0 }} <span class="text-xs text-slate-400 font-normal">/ {{ $student->progress?->theory_classes_required ?? 5 }} classes</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1.5 overflow-hidden">
                @php $thPct = min(100, round((($student->progress?->theory_classes_attended ?? 0) / max(1, $student->progress?->theory_classes_required ?? 5)) * 100)); @endphp
                <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $thPct }}%"></div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded p-2.5">
            <div class="text-[10px] uppercase font-bold text-slate-400">Overall Course Progress</div>
            <div class="text-base font-bold text-emerald-700 mt-0.5">
                {{ $student->progress?->overall_progress_percentage ?? 0 }}%
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1.5 overflow-hidden">
                <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ $student->progress?->overall_progress_percentage ?? 0 }}%"></div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded p-2.5">
            <div class="text-[10px] uppercase font-bold text-slate-400">RTO DL Test Readiness</div>
            <div class="text-base font-bold mt-0.5 {{ ($student->progress?->dl_test_ready ?? false) ? 'text-emerald-700' : 'text-amber-600' }}">
                {{ ($student->progress?->dl_test_ready ?? false) ? 'Qualified for DL Test' : 'Training in Progress' }}
            </div>
            <div class="text-[10px] text-slate-400">
                LL: {{ $student->learner_license_number ?? 'Not registered' }}
            </div>
        </div>
    </div>

    <!-- Main Grid: Upcoming Schedule & Road Signs Guide -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <!-- Left 2 Cols: Lesson Schedule & History -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Next Upcoming Session -->
            @php
                $upcoming = $student->lessonSchedules->where('status', 'scheduled')->first();
            @endphp
            <div class="bg-white border border-blue-200 rounded-lg p-3 shadow-xs bg-gradient-to-r from-blue-50/50 to-white">
                <div class="flex items-center justify-between pb-2 border-b border-blue-100">
                    <span class="text-xs font-bold text-blue-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Next Scheduled Driving Lesson
                    </span>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-700">Confirmed Booking</span>
                </div>
                @if($upcoming)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-2.5 text-xs">
                    <div>
                        <span class="text-[10px] text-slate-400 block uppercase font-bold">Date & Time</span>
                        <div class="font-bold text-slate-800">{{ $upcoming->scheduled_date->format('l, d M Y') }}</div>
                        <div class="text-[11px] font-mono text-blue-700 font-semibold">{{ substr($upcoming->start_time, 0, 5) }} - {{ substr($upcoming->end_time, 0, 5) }}</div>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block uppercase font-bold">Trainer & Contact</span>
                        <div class="font-bold text-slate-800">{{ $upcoming->instructor?->full_name ?? 'Assigned Instructor' }}</div>
                        <div class="text-[11px] text-slate-500">{{ $upcoming->instructor?->phone ?? 'Contact Reception' }}</div>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 block uppercase font-bold">Dual-Control Vehicle</span>
                        <div class="font-bold text-slate-800">{{ $upcoming->vehicle?->registration_number ?? 'Training Fleet' }}</div>
                        <div class="text-[11px] text-slate-500">{{ $upcoming->vehicle?->make_model }} ({{ $upcoming->vehicle?->transmission_type }})</div>
                    </div>
                </div>
                @else
                <div class="py-4 text-center text-xs text-slate-400">
                    No upcoming practical session scheduled right now. Contact academy desk to book your next slot!
                </div>
                @endif
            </div>

            <!-- Completed Practical Lesson Timeline -->
            <div class="bg-white border border-slate-200 rounded overflow-hidden shadow-sm">
                <div class="p-2.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Practical Driving Log & Attendance</span>
                    <span class="text-[10px] text-slate-400">{{ $student->lessonSchedules->count() }} Sessions Total</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase text-[10px] tracking-wider">
                            <tr>
                                <th class="py-2 px-3">Date & Slot</th>
                                <th class="py-2 px-3">Dual-Control Car</th>
                                <th class="py-2 px-3">Instructor</th>
                                <th class="py-2 px-3 text-center">Duration</th>
                                <th class="py-2 px-3 text-center">Odometer (KM)</th>
                                <th class="py-2 px-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($student->lessonSchedules as $lesson)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-2 px-3">
                                    <div class="font-semibold text-slate-800">{{ $lesson->scheduled_date->format('d M Y') }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">{{ substr($lesson->start_time, 0, 5) }} - {{ substr($lesson->end_time, 0, 5) }}</div>
                                </td>
                                <td class="py-2 px-3">
                                    <div class="font-medium text-slate-700 font-mono">{{ $lesson->vehicle?->registration_number }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $lesson->vehicle?->make_model }}</div>
                                </td>
                                <td class="py-2 px-3 text-slate-700">
                                    {{ $lesson->instructor?->full_name }}
                                </td>
                                <td class="py-2 px-3 text-center font-mono">
                                    {{ $lesson->duration_minutes }} min
                                </td>
                                <td class="py-2 px-3 text-center font-mono text-[11px] text-slate-600">
                                    @if($lesson->attendance)
                                        {{ $lesson->attendance->km_covered ? $lesson->attendance->km_covered . ' km' : '-' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-2 px-3">
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold uppercase
                                        {{ $lesson->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                        {{ $lesson->status === 'scheduled' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                        {{ $lesson->status === 'cancelled' ? 'bg-rose-50 text-rose-700 border border-rose-200' : '' }}">
                                        {{ $lesson->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400 text-xs">No lessons logged yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Invoices & Payment Receipts -->
            <div class="bg-white border border-slate-200 rounded overflow-hidden shadow-sm">
                <div class="p-2.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">Fee Invoices & Payment Receipts</span>
                    <span class="text-[10px] text-slate-400">GST SAC 999293 Tax Vouchers</span>
                </div>
                <div class="p-3">
                    @if($student->latestAdmission && $student->latestAdmission->invoices->count() > 0)
                        <div class="space-y-2">
                            @foreach($student->latestAdmission->invoices as $inv)
                            <div class="p-2.5 border border-slate-200 rounded flex items-center justify-between bg-slate-50/50">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-xs text-slate-800 font-mono">{{ $inv->invoice_number }}</span>
                                        <span class="text-[10px] font-bold px-1.5 py-0.2 rounded uppercase {{ $inv->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $inv->payment_status }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        Total: <strong>₹{{ number_format($inv->total_amount, 2) }}</strong> (incl. 18% GST) | Paid: <strong class="text-emerald-700">₹{{ number_format($inv->paid_amount, 2) }}</strong> | Due: <strong class="text-rose-700">₹{{ number_format($inv->due_amount, 2) }}</strong>
                                    </div>
                                </div>
                                <a href="{{ route('billing.invoices.show', $inv->id) }}" target="_blank" class="px-2.5 py-1 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 rounded text-xs font-medium transition-colors">
                                    View GST Invoice
                                </a>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-xs text-slate-400">No invoices generated yet.</div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Right 1 Col: RTO Traffic Signs Cheat Sheet & Quick Tips -->
        <div class="space-y-4">
            
            <!-- RTO Exam Ready Box -->
            <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-xs">
                <div class="flex items-center gap-2 pb-2 border-b border-slate-200">
                    <div class="w-7 h-7 rounded bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">
                        RTO
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-800">Motor Driving Test Prep</h3>
                        <p class="text-[10px] text-slate-500">Essential rules for Section 112/115 MV Act</p>
                    </div>
                </div>
                
                <div class="mt-3 space-y-2 text-xs">
                    <div class="p-2 bg-slate-50 rounded border border-slate-200">
                        <div class="font-bold text-[11px] text-slate-700 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                            3-Point Turn & Parallel Parking
                        </div>
                        <p class="text-[10px] text-slate-500 mt-0.5">Maintain 1-meter curb distance. Check wing mirrors and blind spot before steering.</p>
                    </div>

                    <div class="p-2 bg-slate-50 rounded border border-slate-200">
                        <div class="font-bold text-[11px] text-slate-700 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                            Gradient / Hill Start (Handbrake Control)
                        </div>
                        <p class="text-[10px] text-slate-500 mt-0.5">Depress clutch, engage 1st gear, raise RPM gently to bite point, release handbrake without rolling back.</p>
                    </div>

                    <div class="p-2 bg-slate-50 rounded border border-slate-200">
                        <div class="font-bold text-[11px] text-slate-700 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                            '8' & 'H' Track Demonstration
                        </div>
                        <p class="text-[10px] text-slate-500 mt-0.5">Smooth continuous speed without touching border poles or stopping mid-track.</p>
                    </div>
                </div>
            </div>

            <!-- Mandatory & Cautionary Road Signs Quick Reference -->
            <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-xs">
                <h3 class="text-xs font-bold text-slate-800 mb-2">Mandatory Road Signs (Octagon / Circular)</h3>
                <div class="grid grid-cols-3 gap-2 text-center text-[10px]">
                    <div class="p-2 border border-slate-200 rounded bg-rose-50/50 flex flex-col items-center">
                        <div class="w-7 h-7 rounded-full bg-rose-600 text-white font-bold flex items-center justify-center text-[9px] mb-1">
                            STOP
                        </div>
                        <span class="font-semibold text-slate-700">Stop Sign</span>
                    </div>
                    <div class="p-2 border border-slate-200 rounded bg-rose-50/50 flex flex-col items-center">
                        <div class="w-7 h-7 rounded-full border-2 border-rose-600 bg-white text-rose-600 font-bold flex items-center justify-center text-[9px] mb-1">
                            50
                        </div>
                        <span class="font-semibold text-slate-700">Speed 50</span>
                    </div>
                    <div class="p-2 border border-slate-200 rounded bg-rose-50/50 flex flex-col items-center">
                        <div class="w-7 h-7 rounded-full bg-rose-600 text-white font-bold flex items-center justify-center text-[9px] mb-1">
                            &times;
                        </div>
                        <span class="font-semibold text-slate-700">No Entry</span>
                    </div>
                </div>

                <h3 class="text-xs font-bold text-slate-800 mt-3 mb-2">Cautionary Road Signs (Triangular)</h3>
                <div class="grid grid-cols-3 gap-2 text-center text-[10px]">
                    <div class="p-2 border border-slate-200 rounded bg-amber-50/50 flex flex-col items-center">
                        <div class="w-7 h-7 text-amber-600 font-bold flex items-center justify-center text-sm mb-1">
                            &#9650;
                        </div>
                        <span class="font-semibold text-slate-700">Right Curve</span>
                    </div>
                    <div class="p-2 border border-slate-200 rounded bg-amber-50/50 flex flex-col items-center">
                        <div class="w-7 h-7 text-amber-600 font-bold flex items-center justify-center text-sm mb-1">
                            &#128694;
                        </div>
                        <span class="font-semibold text-slate-700">Pedestrian</span>
                    </div>
                    <div class="p-2 border border-slate-200 rounded bg-amber-50/50 flex flex-col items-center">
                        <div class="w-7 h-7 text-amber-600 font-bold flex items-center justify-center text-sm mb-1">
                            &#9654;
                        </div>
                        <span class="font-semibold text-slate-700">Steep Hill</span>
                    </div>
                </div>
            </div>

            <!-- Academy Helpline Box -->
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs">
                <div class="font-bold text-slate-800">Need Help or Slot Reschedule?</div>
                <p class="text-[11px] text-slate-500 mt-1">Our training coordinators are available from 6:00 AM to 8:00 PM.</p>
                <div class="mt-2 text-[11px] text-blue-700 font-semibold">
                    📞 Desk: +91 98765 43210 / WhatsApp
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
