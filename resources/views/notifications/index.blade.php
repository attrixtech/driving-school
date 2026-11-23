@extends('layouts.app')

@section('title', 'Communications & Notification Gateway')

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-slate-800">Communication & Notification Gateway</h1>
            <p class="text-xs text-slate-500">Automated SMS, WhatsApp Business, and Email notifications dispatched to students and staff.</p>
        </div>
        <button onclick="document.getElementById('sendNotificationModal').classList.remove('hidden')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded font-medium text-xs shadow-sm flex items-center gap-1.5 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
            <span>Dispatch Instant Alert</span>
        </button>
    </div>

    <!-- Gateway Health & Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5">
        <div class="bg-white border border-slate-200 rounded p-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-bold text-slate-400">Total Transmissions</span>
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="text-base font-bold text-slate-800 mt-0.5">{{ $stats['total_sent'] }}</div>
            <div class="text-[10px] text-slate-400">All channels combined</div>
        </div>
        <div class="bg-white border border-slate-200 rounded p-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-bold text-slate-400">WhatsApp Gateway</span>
                <span class="text-[9px] font-bold px-1.5 py-0.2 bg-emerald-100 text-emerald-700 rounded">API Connected</span>
            </div>
            <div class="text-base font-bold text-emerald-700 mt-0.5">{{ $stats['whatsapp_count'] }}</div>
            <div class="text-[10px] text-slate-400">Template messages sent</div>
        </div>
        <div class="bg-white border border-slate-200 rounded p-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-bold text-slate-400">SMS Gateway (DLT)</span>
                <span class="text-[9px] font-bold px-1.5 py-0.2 bg-blue-100 text-blue-700 rounded">TRAI Compliant</span>
            </div>
            <div class="text-base font-bold text-blue-700 mt-0.5">{{ $stats['sms_count'] }}</div>
            <div class="text-[10px] text-slate-400">OTP & Flash SMS</div>
        </div>
        <div class="bg-white border border-slate-200 rounded p-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[10px] uppercase font-bold text-slate-400">Email SMTP</span>
                <span class="text-[9px] font-bold px-1.5 py-0.2 bg-purple-100 text-purple-700 rounded">TLS Encrypted</span>
            </div>
            <div class="text-base font-bold text-purple-700 mt-0.5">{{ $stats['email_count'] }}</div>
            <div class="text-[10px] text-slate-400">Invoices & Receipts</div>
        </div>
    </div>

    <!-- Filters & Transmission Log -->
    <div class="bg-white border border-slate-200 rounded overflow-hidden shadow-sm">
        <div class="p-2.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <div class="flex items-center gap-1.5 text-xs">
                <span class="font-semibold text-slate-700">Filter Channel:</span>
                <a href="{{ route('notifications.index') }}" class="px-2 py-0.5 rounded text-[11px] {{ !$type ? 'bg-blue-600 text-white font-medium' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100' }}">All</a>
                <a href="{{ route('notifications.index', ['type' => 'whatsapp']) }}" class="px-2 py-0.5 rounded text-[11px] {{ $type === 'whatsapp' ? 'bg-blue-600 text-white font-medium' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100' }}">WhatsApp</a>
                <a href="{{ route('notifications.index', ['type' => 'sms']) }}" class="px-2 py-0.5 rounded text-[11px] {{ $type === 'sms' ? 'bg-blue-600 text-white font-medium' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100' }}">SMS</a>
                <a href="{{ route('notifications.index', ['type' => 'email']) }}" class="px-2 py-0.5 rounded text-[11px] {{ $type === 'email' ? 'bg-blue-600 text-white font-medium' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100' }}">Email</a>
            </div>
            <span class="text-[11px] text-slate-500">Live Delivery Log</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="py-2 px-3">Timestamp</th>
                        <th class="py-2 px-3">Channel</th>
                        <th class="py-2 px-3">Title / Event</th>
                        <th class="py-2 px-3">Message Payload</th>
                        <th class="py-2 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($notifications as $item)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-2 px-3 text-[11px] text-slate-500 font-mono whitespace-nowrap">
                            {{ $item->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td class="py-2 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $item->type === 'whatsapp' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                                {{ $item->type === 'sms' ? 'bg-blue-50 text-blue-700 border border-blue-200' : '' }}
                                {{ $item->type === 'email' ? 'bg-purple-50 text-purple-700 border border-purple-200' : '' }}
                                {{ $item->type === 'system' ? 'bg-slate-100 text-slate-700 border border-slate-300' : '' }}">
                                {{ $item->type }}
                            </span>
                        </td>
                        <td class="py-2 px-3 font-semibold text-slate-800">
                            {{ $item->title }}
                        </td>
                        <td class="py-2 px-3 text-slate-600 max-w-md truncate" title="{{ $item->message }}">
                            {{ $item->message }}
                        </td>
                        <td class="py-2 px-3">
                            <span class="inline-flex items-center gap-1 text-[10px] font-medium text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Delivered
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400 text-xs">No communications dispatched yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($notifications->hasPages())
        <div class="p-2.5 border-t border-slate-200 bg-slate-50">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal: Dispatch Instant Alert -->
<div id="sendNotificationModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-3">
    <div class="bg-white rounded-lg border border-slate-200 shadow-xl max-w-md w-full p-4">
        <div class="flex items-center justify-between pb-2 border-b border-slate-200">
            <h3 class="text-sm font-bold text-slate-800">Dispatch Simulated Alert / SMS / WhatsApp</h3>
            <button onclick="document.getElementById('sendNotificationModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form method="POST" action="{{ route('notifications.send') }}" class="mt-3 space-y-2.5">
            @csrf
            <div>
                <label class="block text-[11px] font-medium text-slate-700">Recipient Student *</label>
                <select name="student_id" required class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none">
                    <option value="">-- Choose Student --</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">{{ $student->student_code }} - {{ $student->full_name }} ({{ $student->phone }})</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-medium text-slate-700">Gateway Channel *</label>
                    <select name="channel" required class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none">
                        <option value="whatsapp">WhatsApp Business</option>
                        <option value="sms">SMS (DLT Template)</option>
                        <option value="email">Email</option>
                        <option value="system">In-App Alert</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-700">Message Template *</label>
                    <select name="template" required class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none">
                        <option value="lesson_reminder">Tomorrow Practical Lesson</option>
                        <option value="fee_reminder">Course Fee Balance Reminder</option>
                        <option value="ll_expiry">Learner License Expiry Notice</option>
                        <option value="form5_ready">Form 5 Certificate Ready</option>
                        <option value="custom">Custom Message</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-[11px] font-medium text-slate-700">Custom Remarks / Body (Optional)</label>
                <textarea name="custom_message" rows="2" placeholder="Only needed if custom template selected..." class="w-full text-xs px-2.5 py-1.5 border border-slate-300 rounded focus:border-blue-500 focus:outline-none"></textarea>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200">
                <button type="button" onclick="document.getElementById('sendNotificationModal').classList.add('hidden')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium">Cancel</button>
                <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium">Transmit Message</button>
            </div>
        </form>
    </div>
</div>
@endsection
