<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\MockTest;
use App\Models\Student;
use App\Models\StudentProgress;

class ProgressTrackerService
{
    /**
     * Records a mock test attempt and evaluates passing score.
     */
    public function recordMockTest(array $data): MockTest
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $totalQuestions = intval($data['total_questions'] ?? 20);
        $correct = intval($data['correct_answers']);
        $passingScore = intval($data['passing_score'] ?? 12);

        $percentage = round(($correct / max(1, $totalQuestions)) * 100, 2);
        $isPassed = $correct >= $passingScore;

        $test = MockTest::create([
            'tenant_id' => $tenantId,
            'student_id' => $data['student_id'],
            'test_title' => $data['test_title'] ?? 'RTO Learner License Mock Test',
            'category' => $data['category'] ?? 'comprehensive_rto',
            'total_questions' => $totalQuestions,
            'correct_answers' => $correct,
            'passing_score' => $passingScore,
            'score_percentage' => $percentage,
            'is_passed' => $isPassed,
            'time_taken_seconds' => $data['time_taken_seconds'] ?? 600,
            'remarks' => $data['remarks'] ?? ($isPassed ? 'Passed mock test successfully' : 'Needs more practice on road signals'),
        ]);

        $progress = StudentProgress::where('student_id', $data['student_id'])->first();
        if ($progress) {
            $progress->recalculate();
        }

        ActivityLog::record(
            'training',
            'mock_test',
            "Recorded mock test for student #{$data['student_id']}: {$correct}/{$totalQuestions} (" . ($isPassed ? 'PASSED' : 'FAILED') . ")",
            ['mock_test_id' => $test->id]
        );

        return $test;
    }

    /**
     * Updates student license status.
     */
    public function updateLicenseDetails(Student $student, array $data): Student
    {
        $student->update([
            'learner_license_number' => $data['learner_license_number'] ?? $student->learner_license_number,
            'll_issue_date' => $data['ll_issue_date'] ?? $student->ll_issue_date,
            'll_expiry_date' => $data['ll_expiry_date'] ?? $student->ll_expiry_date,
            'permanent_license_number' => $data['permanent_license_number'] ?? $student->permanent_license_number,
            'dl_test_date' => $data['dl_test_date'] ?? $student->dl_test_date,
            'dl_rto_office' => $data['dl_rto_office'] ?? $student->dl_rto_office,
            'dl_status' => $data['dl_status'] ?? $student->dl_status,
        ]);

        ActivityLog::record(
            'licensing',
            'update',
            "Updated license details for student {$student->full_name} (DL Status: {$student->dl_status})",
            ['student_id' => $student->id]
        );

        return $student;
    }
}
