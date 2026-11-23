@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ newLeadModal: {{ request()->has('new') ? 'true' : 'false' }}, followupModal: false, activeLead: null }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Enquiries & Leads CRM</h1>
            <p class="text-xs text-slate-500">Capture, follow up, and convert prospects into admitted students.</p>
        </div>
        <button @click="newLeadModal = true" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ New Enquiry</span>
        </button>
    </div>

    <!-- Filter Status Tabs -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
        <a href="{{ route('crm.leads') }}" class="px-2.5 py-1 rounded font-medium transition-colors {{ empty($status) ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            All Leads
        </a>
        <a href="{{ route('crm.leads', ['status' => 'new']) }}" class="px-2.5 py-1 rounded font-medium transition-colors {{ $status === 'new' ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            New
        </a>
        <a href="{{ route('crm.leads', ['status' => 'contacted']) }}" class="px-2.5 py-1 rounded font-medium transition-colors {{ $status === 'contacted' ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Contacted
        </a>
        <a href="{{ route('crm.leads', ['status' => 'interested']) }}" class="px-2.5 py-1 rounded font-medium transition-colors {{ $status === 'interested' ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Interested
        </a>
        <a href="{{ route('crm.leads', ['status' => 'admitted']) }}" class="px-2.5 py-1 rounded font-medium transition-colors {{ $status === 'admitted' ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Admitted
        </a>
        <a href="{{ route('crm.leads', ['status' => 'lost']) }}" class="px-2.5 py-1 rounded font-medium transition-colors {{ $status === 'lost' ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            Lost
        </a>
    </div>

    <!-- Leads Table -->
    <div class="bg-white border border-slate-200 rounded-md shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-[10px] uppercase font-bold text-slate-400 border-b border-slate-200">
                        <th class="py-2 px-3">Lead Ref</th>
                        <th class="py-2 px-3">Prospect Name</th>
                        <th class="py-2 px-3">Contact</th>
                        <th class="py-2 px-3">Course Interested</th>
                        <th class="py-2 px-3">Source</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3">Follow-up</th>
                        <th class="py-2 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-2 px-3 font-mono text-[11px] font-semibold text-slate-800">
                                {{ $lead->lead_number }}
                            </td>
                            <td class="py-2 px-3">
                                <div class="font-bold text-slate-900">{{ $lead->full_name }}</div>
                                @if($lead->city)
                                    <div class="text-[10px] text-slate-400">{{ $lead->city }}</div>
                                @endif
                            </td>
                            <td class="py-2 px-3 whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $lead->phone }}</div>
                                <div class="text-[10px] text-slate-400">{{ $lead->email ?? 'No email' }}</div>
                            </td>
                            <td class="py-2 px-3">
                                <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 font-medium rounded text-[11px] border border-blue-100">
                                    {{ $lead->course?->name ?? 'General Inquiry' }}
                                </span>
                            </td>
                            <td class="py-2 px-3 capitalize text-slate-600">
                                {{ str_replace('_', ' ', $lead->source) }}
                            </td>
                            <td class="py-2 px-3 whitespace-nowrap">
                                @php
                                    $statusClasses = [
                                        'new' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'contacted' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'interested' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'trial_booked' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'admitted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'lost' => 'bg-slate-100 text-slate-600 border-slate-200',
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold uppercase tracking-wider border {{ $statusClasses[$lead->status] ?? 'bg-slate-100' }}">
                                    {{ str_replace('_', ' ', $lead->status) }}
                                </span>
                            </td>
                            <td class="py-2 px-3 whitespace-nowrap">
                                @if($lead->follow_up_date)
                                    <span class="font-medium {{ \Carbon\Carbon::parse($lead->follow_up_date)->isPast() ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                        {{ \Carbon\Carbon::parse($lead->follow_up_date)->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">None set</span>
                                @endif
                                <div class="text-[10px] text-slate-400">{{ $lead->followups->count() }} followups</div>
                            </td>
                            <td class="py-2 px-3 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="activeLead = {{ $lead->toJson() }}; followupModal = true" class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-medium border border-slate-200">
                                        Log Call
                                    </button>
                                    @if($lead->status !== 'admitted')
                                        <a href="{{ route('admissions.create') }}?lead_id={{ $lead->id }}" class="px-2 py-0.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-[11px] font-semibold">
                                            Admit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-xs text-slate-400 italic">
                                No leads found matching criteria. Click "+ New Enquiry" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
            <div class="px-3 py-2 border-t border-slate-200 bg-slate-50">
                {{ $leads->links() }}
            </div>
        @endif
    </div>

    <!-- NEW LEAD MODAL -->
    <div x-show="newLeadModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="newLeadModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Record New Student Enquiry</h3>
                <button @click="newLeadModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>
            
            <form method="POST" action="{{ route('crm.leads.store') }}" class="p-4 space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Full Name *</label>
                        <input type="text" name="full_name" required placeholder="e.g. Ramesh Chandra" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Phone Number *</label>
                        <input type="text" name="phone" required placeholder="+91 98765 00000" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="email" placeholder="name@example.com" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">City / Area</label>
                        <input type="text" name="city" placeholder="Bengaluru" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Course Interested In</label>
                        <select name="course_id" class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="">-- Select Course --</option>
                            @foreach($courses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (₹{{ number_format($c->default_fee) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Lead Source *</label>
                        <select name="source" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="walkin">Walk-in Inquiry</option>
                            <option value="phone">Phone Call</option>
                            <option value="website">Website / Online</option>
                            <option value="referral">Friend / Student Referral</option>
                            <option value="social_media">Instagram / Facebook</option>
                            <option value="justdial">Justdial / Google My Business</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Initial Status *</label>
                        <select name="status" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="new">New Enquiry</option>
                            <option value="contacted">Contacted</option>
                            <option value="interested" selected>Interested</option>
                            <option value="trial_booked">Trial / Demo Booked</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Next Follow-up Date</label>
                        <input type="date" name="follow_up_date" value="{{ now()->addDay()->toDateString() }}" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Inquiry Requirements / Notes</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Needs morning 7 AM slot, prefers female instructor or automatic car..." class="w-full form-textarea border border-slate-300 rounded px-2.5 py-1.5 text-xs"></textarea>
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="newLeadModal = false" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold">Save Enquiry</button>
                </div>
            </form>
        </div>
    </div>

    <!-- LOG FOLLOW-UP MODAL -->
    <div x-show="followupModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="followupModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">
                    Log Interaction: <span x-text="activeLead ? activeLead.full_name : ''"></span>
                </h3>
                <button @click="followupModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form :action="'/crm/leads/' + (activeLead ? activeLead.id : 0) + '/followup'" method="POST" class="p-4 space-y-3 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Communication Channel *</label>
                        <select name="communication_channel" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="call">Phone Call</option>
                            <option value="whatsapp">WhatsApp Message</option>
                            <option value="in_person">In-Person Visit</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Outcome *</label>
                        <select name="outcome" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="connected">Connected & Discussed</option>
                            <option value="callback_requested">Callback Requested</option>
                            <option value="no_answer">No Answer / Busy</option>
                            <option value="enrolled">Agreed to Enroll</option>
                            <option value="not_interested">Not Interested</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Call Notes / Discussion Summary *</label>
                    <textarea name="notes" required rows="3" placeholder="Summary of discussion with the prospect..." class="w-full form-textarea border border-slate-300 rounded px-2.5 py-1.5 text-xs"></textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Next Follow-up Date</label>
                    <input type="date" name="next_followup_date" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="followupModal = false" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold">Save Interaction</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
