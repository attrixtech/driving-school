<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Lead;
use App\Models\LeadFollowup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;
        $status = $request->query('status');
        $search = $request->query('search');
        $filter = $request->query('filter');

        $query = Lead::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('lead_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->when($filter === 'today', function ($q) {
                $q->whereDate('follow_up_date', now()->toDateString());
            })
            ->when($filter === 'overdue', function ($q) {
                $q->whereDate('follow_up_date', '<', now()->toDateString())->whereNotIn('status', ['admitted', 'lost']);
            });

        $leads = $query->with([
            'course',
            'assignedUser',
            'followups' => fn($q) => $q->orderBy('followup_at', 'desc')->with('user')
        ])
        ->latest()
        ->paginate(15);

        // CRM Telemetry
        $stats = [
            'total_leads' => Lead::where('tenant_id', $tenantId)->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'total_call_logs' => LeadFollowup::where('tenant_id', $tenantId)->count(),
            'followups_due_today' => Lead::where('tenant_id', $tenantId)->when($branchId, fn($q) => $q->where('branch_id', $branchId))->whereDate('follow_up_date', now()->toDateString())->count(),
            'overdue_followups' => Lead::where('tenant_id', $tenantId)->when($branchId, fn($q) => $q->where('branch_id', $branchId))->whereDate('follow_up_date', '<', now()->toDateString())->whereNotIn('status', ['admitted', 'lost'])->count(),
            'admitted' => Lead::where('tenant_id', $tenantId)->when($branchId, fn($q) => $q->where('branch_id', $branchId))->where('status', 'admitted')->count(),
        ];

        $courses = Course::where('tenant_id', $tenantId)->where('is_active', true)->get();
        $branches = Branch::where('tenant_id', $tenantId)->where('is_active', true)->get();

        return view('crm.leads', compact('leads', 'courses', 'branches', 'status', 'search', 'filter', 'stats'));
    }

    public function store(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = $request->input('branch_id') ?? session('active_branch_id') ?? auth()->user()?->branch_id;

        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'phone' => 'required|string|max:25',
            'email' => 'nullable|email|max:150',
            'city' => 'nullable|string|max:100',
            'course_id' => 'nullable|exists:courses,id',
            'source' => 'required|in:walkin,website,phone,referral,social_media,justdial,other',
            'status' => 'required|in:new,contacted,interested,trial_booked,admitted,lost',
            'follow_up_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $leadNumber = 'LEAD-' . date('Y') . '-' . strtoupper(Str::random(5));

        $lead = Lead::create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'lead_number' => $leadNumber,
            'assigned_to_user_id' => auth()->id(),
            ...$validated,
        ]);

        ActivityLog::record('crm', 'create', "Captured new lead {$lead->full_name} ({$lead->lead_number})", ['lead_id' => $lead->id]);

        return redirect()->route('crm.leads')->with('success', "Lead {$lead->full_name} recorded successfully (Ref: {$lead->lead_number}).");
    }

    public function addFollowup(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'communication_channel' => 'required|in:call,whatsapp,email,in_person,sms',
            'outcome' => 'required|in:connected,no_answer,callback_requested,not_interested,enrolled',
            'notes' => 'required|string',
            'next_followup_date' => 'nullable|date',
        ]);

        LeadFollowup::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'followup_at' => now(),
            ...$validated,
        ]);

        if (!empty($validated['next_followup_date'])) {
            $lead->update(['follow_up_date' => $validated['next_followup_date']]);
        }

        if ($validated['outcome'] === 'enrolled') {
            $lead->update(['status' => 'admitted']);
        } elseif ($validated['outcome'] === 'not_interested') {
            $lead->update(['status' => 'lost']);
        } else {
            $lead->update(['status' => 'contacted']);
        }

        return redirect()->back()->with('success', 'Follow-up interaction logged.');
    }

    public function getLogs(Lead $lead)
    {
        $lead->load([
            'followups' => fn($q) => $q->orderBy('followup_at', 'desc')->with('user'),
            'course',
            'assignedUser',
            'branch'
        ]);

        return response()->json([
            'lead' => $lead,
            'followups' => $lead->followups,
        ]);
    }
}
