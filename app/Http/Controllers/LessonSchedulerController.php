<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Branch;
use App\Models\Instructor;
use App\Models\LessonSchedule;
use App\Models\Student;
use App\Models\Vehicle;
use App\Services\LessonSchedulerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LessonSchedulerController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;
        $date = $request->query('date', now()->toDateString());
        $status = $request->query('status');

        $schedules = LessonSchedule::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($date, fn($q) => $q->whereDate('scheduled_date', $date))
            ->when($status, fn($q) => $q->where('status', $status))
            ->with(['student', 'instructor', 'vehicle', 'attendance'])
            ->orderBy('start_time')
            ->paginate(20);

        $instructors = Instructor::where('tenant_id', $tenantId)->get();
        $vehicles = Vehicle::where('tenant_id', $tenantId)->get();

        return view('schedules.index', compact('schedules', 'date', 'status', 'instructors', 'vehicles'));
    }

    public function calendar(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;
        $currentDate = $request->query('date', now()->toDateString());

        // Fetch 7 days starting from Monday of this week
        $startOfWeek = Carbon::parse($currentDate)->startOfWeek();
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = (clone $startOfWeek)->addDays($i);
            $weekDays[] = [
                'date' => $day->toDateString(),
                'dayName' => $day->format('D'),
                'dayNumber' => $day->format('d M'),
                'isToday' => $day->isToday(),
            ];
        }

        $schedules = LessonSchedule::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('scheduled_date', [$startOfWeek->toDateString(), (clone $startOfWeek)->addDays(6)->toDateString()])
            ->with(['student', 'instructor', 'vehicle'])
            ->orderBy('start_time')
            ->get();

        return view('schedules.calendar', compact('weekDays', 'schedules', 'startOfWeek', 'currentDate'));
    }

    public function create(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        $studentId = $request->query('student_id');
        $student = $studentId ? Student::where('tenant_id', $tenantId)->find($studentId) : null;
        $admission = $student?->latestAdmission;

        $students = Student::where('tenant_id', $tenantId)
            ->whereIn('status', ['enrolled', 'in_training'])
            ->with('latestAdmission')
            ->get();

        $instructors = Instructor::where('tenant_id', $tenantId)->where('status', 'active')->get();
        $vehicles = Vehicle::where('tenant_id', $tenantId)->where('status', 'active')->get();
        $branches = Branch::where('tenant_id', $tenantId)->where('is_active', true)->get();

        return view('schedules.create', compact('students', 'instructors', 'vehicles', 'branches', 'student', 'admission', 'branchId'));
    }

    public function store(Request $request, LessonSchedulerService $schedulerService)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'student_id' => 'required|exists:students,id',
            'admission_id' => 'nullable|exists:admissions,id',
            'instructor_id' => 'required|exists:instructors,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'lesson_type' => 'required|in:practical,theory,simulator,assessment,mock_test',
            'topic' => 'nullable|string|max:150',
            'scheduled_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'notes' => 'nullable|string',
        ]);

        $schedule = $schedulerService->scheduleLesson($validated);

        return redirect()->route('schedules.index', ['date' => $schedule->scheduled_date->toDateString()])
            ->with('success', "Lesson successfully scheduled for {$schedule->student?->full_name} with {$schedule->instructor?->name}.");
    }

    public function completeForm(LessonSchedule $schedule)
    {
        $schedule->load(['student', 'instructor', 'vehicle', 'attendance']);
        return view('schedules.complete', compact('schedule'));
    }

    public function complete(Request $request, LessonSchedule $schedule, LessonSchedulerService $schedulerService)
    {
        $validated = $request->validate([
            'status' => 'required|in:present,absent,late',
            'actual_start_time' => 'nullable',
            'actual_end_time' => 'nullable',
            'start_odometer' => 'nullable|integer|min:0',
            'end_odometer' => 'nullable|integer|min:0',
            'student_rating' => 'nullable|integer|min:1|max:5',
            'student_feedback' => 'nullable|string',
            'instructor_notes' => 'nullable|string',
        ]);

        $schedulerService->completeLesson($schedule, $validated);

        return redirect()->route('schedules.index')
            ->with('success', "Lesson marked completed. Attendance and training hours updated for {$schedule->student?->full_name}.");
    }
}
