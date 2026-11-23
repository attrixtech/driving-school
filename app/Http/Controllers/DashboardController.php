<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Instructor;
use App\Models\Lead;
use App\Models\LessonSchedule;
use App\Models\Student;
use App\Models\Vehicle;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, BillingService $billingService)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        // Base Queries with branch scope
        $studentQuery = Student::where('tenant_id', $tenantId)->when($branchId, fn($q) => $q->where('branch_id', $branchId));
        $admissionQuery = Admission::where('tenant_id', $tenantId)->when($branchId, fn($q) => $q->where('branch_id', $branchId));
        $scheduleQuery = LessonSchedule::where('tenant_id', $tenantId)->when($branchId, fn($q) => $q->where('branch_id', $branchId));
        $vehicleQuery = Vehicle::where('tenant_id', $tenantId)->when($branchId, fn($q) => $q->where('branch_id', $branchId));
        $instructorQuery = Instructor::where('tenant_id', $tenantId)->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        // 1. Key Statistics
        $totalStudents = (clone $studentQuery)->count();
        $activeStudents = (clone $studentQuery)->whereIn('status', ['enrolled', 'in_training'])->count();
        $completedStudents = (clone $studentQuery)->where('status', 'completed')->count();
        $newAdmissionsToday = (clone $admissionQuery)->whereDate('admission_date', now()->toDateString())->count();

        // 2. Instructor & Vehicle Availability
        $totalInstructors = (clone $instructorQuery)->count();
        $availableInstructors = (clone $instructorQuery)->where('status', 'active')->count();

        $totalVehicles = (clone $vehicleQuery)->count();
        $availableVehicles = (clone $vehicleQuery)->where('status', 'active')->count();

        // 3. Today's Lesson Schedules
        $todaySchedules = (clone $scheduleQuery)
            ->whereDate('scheduled_date', now()->toDateString())
            ->with(['student', 'instructor', 'vehicle'])
            ->orderBy('start_time')
            ->get();

        $todayScheduledCount = $todaySchedules->count();
        $todayCompletedCount = $todaySchedules->where('status', 'completed')->count();
        $todayMissedCount = $todaySchedules->where('status', 'missed')->count();

        // 4. Financial Metrics from BillingService
        $fin = $billingService->getFinancialMetrics($branchId);

        // 5. Actionable Alerts (Section 5 requirements)
        $alerts = [];

        // Vehicles with documents expiring within 20 days
        $expiringVehicles = (clone $vehicleQuery)
            ->where(function ($q) {
                $threshold = now()->addDays(20);
                $q->where(fn($sub) => $sub->whereNotNull('pollution_expiry_date')->where('pollution_expiry_date', '<=', $threshold))
                  ->orWhere(fn($sub) => $sub->whereNotNull('insurance_expiry_date')->where('insurance_expiry_date', '<=', $threshold))
                  ->orWhere(fn($sub) => $sub->whereNotNull('fitness_expiry_date')->where('fitness_expiry_date', '<=', $threshold));
            })
            ->get();

        foreach ($expiringVehicles as $v) {
            if ($v->pollution_expiry_date && $v->pollution_expiry_date <= now()->addDays(20)) {
                $days = now()->diffInDays($v->pollution_expiry_date, false);
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'PUC Expiry Alert',
                    'message' => "Vehicle {$v->registration_number} ({$v->model}) PUC expires in " . max(0, $days) . " days ({$v->pollution_expiry_date->format('d M Y')}).",
                    'link' => route('vehicles.index'),
                ];
            }
            if ($v->insurance_expiry_date && $v->insurance_expiry_date <= now()->addDays(20)) {
                $days = now()->diffInDays($v->insurance_expiry_date, false);
                $alerts[] = [
                    'type' => 'danger',
                    'title' => 'Insurance Expiry Alert',
                    'message' => "Vehicle {$v->registration_number} Insurance expires in " . max(0, $days) . " days ({$v->insurance_expiry_date->format('d M Y')}).",
                    'link' => route('vehicles.index'),
                ];
            }
        }

        // Students with Learner License expiring within 20 days
        $expiringLLStudents = (clone $studentQuery)
            ->whereNotNull('ll_expiry_date')
            ->where('ll_expiry_date', '<=', now()->addDays(20))
            ->where('status', '!=', 'completed')
            ->get();

        foreach ($expiringLLStudents as $s) {
            $days = now()->diffInDays($s->ll_expiry_date, false);
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Student LL Expiry Alert',
                'message' => "Student {$s->full_name} ({$s->student_code}) Learner License expires in " . max(0, $days) . " days ({$s->ll_expiry_date->format('d M Y')}). Needs test scheduling.",
                'link' => route('students.show', $s->id),
            ];
        }

        // 6. Recent Admissions
        $recentAdmissions = (clone $admissionQuery)
            ->with(['student', 'course', 'package'])
            ->latest()
            ->take(5)
            ->get();

        // 7. Recent Enquiries
        $recentLeads = Lead::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with('course')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalStudents',
            'activeStudents',
            'completedStudents',
            'newAdmissionsToday',
            'totalInstructors',
            'availableInstructors',
            'totalVehicles',
            'availableVehicles',
            'todayScheduledCount',
            'todayCompletedCount',
            'todayMissedCount',
            'todaySchedules',
            'fin',
            'alerts',
            'recentAdmissions',
            'recentLeads'
        ));
    }
}
