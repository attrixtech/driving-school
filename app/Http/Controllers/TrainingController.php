<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Instructor;
use App\Models\MockTest;
use App\Models\Student;
use App\Models\StudentProgress;
use App\Models\TheoryAttendance;
use App\Models\TheoryClass;
use App\Services\ProgressTrackerService;
use App\Services\TheoryCurriculumService;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    public function progress(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        $progressList = StudentProgress::where('tenant_id', $tenantId)
            ->with(['student.branch', 'student.latestAdmission.course', 'admission.package'])
            ->latest()
            ->paginate(20);

        return view('training.progress', compact('progressList'));
    }

    public function mocktests(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $studentId = $request->query('student_id');

        $mockTests = MockTest::where('tenant_id', $tenantId)
            ->when($studentId, fn($q) => $q->where('student_id', $studentId))
            ->with('student')
            ->latest()
            ->paginate(15);

        $students = Student::where('tenant_id', $tenantId)->whereIn('status', ['enrolled', 'in_training'])->get();

        return view('training.mocktests', compact('mockTests', 'students', 'studentId'));
    }

    public function storeMockTest(Request $request, ProgressTrackerService $progressService)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'test_title' => 'required|string|max:150',
            'category' => 'required|in:road_signs,traffic_regulations,vehicle_controls,comprehensive_rto',
            'total_questions' => 'required|integer|min:5|max:100',
            'correct_answers' => 'required|integer|min:0|lte:total_questions',
            'passing_score' => 'required|integer|min:1|lte:total_questions',
            'remarks' => 'nullable|string',
        ]);

        $test = $progressService->recordMockTest($validated);

        return redirect()->back()->with('success', "Mock test result recorded for {$test->student?->full_name}. Result: " . ($test->is_passed ? 'PASSED (Eligible for DL)' : 'FAILED'));
    }

    /**
     * Display the Traffic Signs & Theory Curriculum Learning Hub.
     */
    public function theory(Request $request, TheoryCurriculumService $curriculumService)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        $category = $request->query('category', 'all');
        $search = $request->query('search');
        $activeTab = $request->query('tab', 'signs'); // 'signs', 'modules', 'quiz', 'classes'

        // 1. Fetch Traffic Signs
        $allSigns = $curriculumService->getTrafficSigns();
        $trafficSigns = collect($allSigns);

        if ($category && $category !== 'all') {
            $trafficSigns = $trafficSigns->where('category', $category);
        }

        if ($search) {
            $trafficSigns = $trafficSigns->filter(function ($s) use ($search) {
                return str_contains(strtolower($s['name']), strtolower($search))
                    || str_contains(strtolower($s['description']), strtolower($search))
                    || str_contains(strtolower($s['category']), strtolower($search));
            });
        }

        // 2. Fetch Theory Modules
        $theoryModules = $curriculumService->getTheoryModules();

        // 3. Fetch Practice Quiz
        $practiceQuiz = $curriculumService->getPracticeQuiz();

        // 4. Fetch Theory Classes (Batches)
        $theoryClasses = TheoryClass::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with(['instructor', 'branch', 'attendances.student'])
            ->orderBy('session_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        $students = Student::where('tenant_id', $tenantId)->whereIn('status', ['enrolled', 'in_training'])->orderBy('first_name')->get();
        $instructors = Instructor::where('tenant_id', $tenantId)->where('status', 'active')->orderBy('name')->get();
        $branches = Branch::where('tenant_id', $tenantId)->orderBy('name')->get();

        // Counts for telemetry
        $totalSignsCount = count($allSigns);
        $totalModulesCount = count($theoryModules);
        $upcomingClassesCount = $theoryClasses->where('status', 'scheduled')->count();
        $totalTheoryHoursRequired = 5;

        return view('training.theory', compact(
            'trafficSigns',
            'allSigns',
            'theoryModules',
            'practiceQuiz',
            'theoryClasses',
            'students',
            'instructors',
            'branches',
            'category',
            'search',
            'activeTab',
            'totalSignsCount',
            'totalModulesCount',
            'upcomingClassesCount',
            'totalTheoryHoursRequired'
        ));
    }

    /**
     * Store a scheduled theory class batch.
     */
    public function storeTheoryClass(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'instructor_id' => 'nullable|exists:instructors,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'session_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'max_seats' => 'nullable|integer|min:1|max:100',
            'room_or_link' => 'nullable|string|max:255',
            'status' => 'nullable|in:scheduled,ongoing,completed,cancelled',
        ]);

        $validated['tenant_id'] = $tenantId;
        $validated['max_seats'] = $validated['max_seats'] ?? 30;
        $validated['status'] = $validated['status'] ?? 'scheduled';

        $theoryClass = TheoryClass::create($validated);

        return redirect()->route('training.theory', ['tab' => 'classes'])
            ->with('success', "Theory classroom session '{$theoryClass->title}' scheduled for {$theoryClass->session_date->format('M d, Y')}.");
    }

    /**
     * Record attendance for students attending a theory class.
     */
    public function attendTheoryClass(Request $request, TheoryClass $class)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        if ($class->tenant_id != $tenantId) {
            abort(403);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'status' => 'required|in:present,absent',
        ]);

        TheoryAttendance::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'theory_class_id' => $class->id,
                'student_id' => $validated['student_id'],
            ],
            [
                'status' => $validated['status'],
            ]
        );

        // Recalculate progress for student if progress record exists
        $progress = StudentProgress::where('student_id', $validated['student_id'])->first();
        if ($progress) {
            $progress->completed_theory_hours = min(
                $progress->required_theory_hours,
                $progress->completed_theory_hours + 1.0
            );
            $progress->recalculate();
        }

        $student = Student::find($validated['student_id']);

        return redirect()->back()
            ->with('success', "Theory attendance recorded as " . strtoupper($validated['status']) . " for {$student?->full_name}.");
    }
}
