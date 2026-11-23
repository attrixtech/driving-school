<?php

namespace App\Http\Controllers;

use App\Models\MockTest;
use App\Models\Student;
use App\Models\StudentProgress;
use App\Services\ProgressTrackerService;
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
}
