<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProgress extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'student_progress';
    protected $guarded = ['id'];

    protected $casts = [
        'required_practical_hours' => 'integer',
        'completed_practical_hours' => 'decimal:2',
        'required_theory_hours' => 'integer',
        'completed_theory_hours' => 'decimal:2',
        'mock_tests_passed_count' => 'integer',
        'overall_progress_percentage' => 'decimal:2',
        'eligible_for_dl_test' => 'boolean',
        'course_completed' => 'boolean',
        'completion_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function recalculate(): void
    {
        $practicalHours = LessonSchedule::where('student_id', $this->student_id)
            ->where('status', 'completed')
            ->where('lesson_type', 'practical')
            ->sum('duration_minutes') / 60;

        $theoryHours = LessonSchedule::where('student_id', $this->student_id)
            ->where('status', 'completed')
            ->where('lesson_type', 'theory')
            ->sum('duration_minutes') / 60;

        $mockTestsPassed = MockTest::where('student_id', $this->student_id)
            ->where('is_passed', true)
            ->count();

        $this->completed_practical_hours = $practicalHours;
        $this->completed_theory_hours = $theoryHours;
        $this->mock_tests_passed_count = $mockTestsPassed;

        $reqPrac = max(1, $this->required_practical_hours);
        $reqTheo = max(1, $this->required_theory_hours);

        $pracRatio = min(1.0, $practicalHours / $reqPrac);
        $theoRatio = min(1.0, $theoryHours / $reqTheo);

        // Practical is weighted 70%, theory 30%
        $percentage = round(($pracRatio * 70) + ($theoRatio * 30), 2);
        $this->overall_progress_percentage = $percentage;

        if ($practicalHours >= $this->required_practical_hours && $mockTestsPassed >= 1) {
            $this->eligible_for_dl_test = true;
        }

        if ($percentage >= 100) {
            $this->course_completed = true;
            $this->completion_date = $this->completion_date ?? now()->toDateString();
        }

        $this->save();
    }
}
