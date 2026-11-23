<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Student;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $type = $request->query('type');

        $notifications = Notification::where('tenant_id', $tenantId)
            ->when($type, fn($q) => $q->where('type', $type))
            ->latest()
            ->paginate(20);

        $students = Student::where('tenant_id', $tenantId)->whereNotIn('status', ['dropped'])->get();

        $stats = [
            'total_sent' => Notification::where('tenant_id', $tenantId)->count(),
            'whatsapp_count' => Notification::where('tenant_id', $tenantId)->where('type', 'whatsapp')->count(),
            'sms_count' => Notification::where('tenant_id', $tenantId)->where('type', 'sms')->count(),
            'email_count' => Notification::where('tenant_id', $tenantId)->where('type', 'email')->count(),
        ];

        return view('notifications.index', compact('notifications', 'students', 'stats', 'type'));
    }

    public function send(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'channel' => 'required|in:whatsapp,sms,email,system',
            'template' => 'required|string',
            'custom_message' => 'nullable|string|max:500',
        ]);

        $student = Student::where('tenant_id', $tenantId)->findOrFail($validated['student_id']);

        $messageContent = match ($validated['template']) {
            'lesson_reminder' => "Dear {$student->first_name}, your driving practical lesson is scheduled for tomorrow. Please report to the training ground 10 mins prior. Regards, Apex Driving Academy.",
            'fee_reminder' => "Dear {$student->first_name}, this is a gentle reminder regarding your pending driving course fee balance. Please visit the reception or pay online. Regards, Apex Driving Academy.",
            'll_expiry' => "Dear {$student->first_name}, your Learner's License is approaching expiry. Please consult our licensing desk to book your permanent DL test. Regards, Apex Driving Academy.",
            'form5_ready' => "Congratulations {$student->first_name}! Your Form 5 Driving Training Certificate is generated and verified with QR security. You may download it from your portal.",
            default => $validated['custom_message'] ?? "Notification from Apex Driving Academy.",
        };

        $notification = Notification::create([
            'tenant_id' => $tenantId,
            'user_id' => $student->user_id ?? auth()->id(),
            'type' => $validated['channel'],
            'title' => ucfirst($validated['channel']) . ': ' . str_replace('_', ' ', ucwords($validated['template'])),
            'message' => $messageContent,
            'link' => route('students.show', $student->id),
            'is_read' => false,
        ]);

        ActivityLog::record('notification', 'dispatch', "Dispatched {$validated['channel']} notification to {$student->full_name} ({$validated['template']})");

        return redirect()->back()->with('success', "Message dispatched successfully via {$validated['channel']} gateway to {$student->full_name}.");
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Notification marked as read.');
    }
}
