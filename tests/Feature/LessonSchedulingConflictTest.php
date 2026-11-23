<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Instructor;
use App\Models\LessonSchedule;
use App\Models\Student;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\LessonSchedulerService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LessonSchedulingConflictTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_scheduling_service_detects_instructor_double_booking(): void
    {
        $owner = User::where('email', 'owner@apexdriving.com')->first();
        $this->actingAs($owner);

        $branch = Branch::first();
        $student1 = Student::first();
        $student2 = Student::skip(1)->first();
        $instructor = Instructor::first();
        $vehicle1 = Vehicle::first();
        $vehicle2 = Vehicle::skip(1)->first();

        $service = app(LessonSchedulerService::class);

        // Schedule first session from 14:00 to 15:00
        $schedule1 = $service->scheduleLesson([
            'branch_id' => $branch->id,
            'student_id' => $student1->id,
            'instructor_id' => $instructor->id,
            'vehicle_id' => $vehicle1->id,
            'scheduled_date' => now()->addDays(3)->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'lesson_type' => 'practical',
        ]);

        $this->assertNotNull($schedule1->id);

        // Attempt overlapping session with SAME instructor from 14:30 to 15:30
        $this->expectException(ValidationException::class);

        $service->scheduleLesson([
            'branch_id' => $branch->id,
            'student_id' => $student2->id,
            'instructor_id' => $instructor->id, // SAME INSTRUCTOR
            'vehicle_id' => $vehicle2->id,
            'scheduled_date' => now()->addDays(3)->toDateString(),
            'start_time' => '14:30:00', // OVERLAPPING
            'end_time' => '15:30:00',
            'lesson_type' => 'practical',
        ]);
    }

    public function test_scheduling_service_detects_vehicle_double_booking(): void
    {
        $owner = User::where('email', 'owner@apexdriving.com')->first();
        $this->actingAs($owner);

        $branch = Branch::first();
        $student1 = Student::first();
        $student2 = Student::skip(1)->first();
        $instructor1 = Instructor::first();
        $instructor2 = Instructor::skip(1)->first();
        $vehicle = Vehicle::first();

        $service = app(LessonSchedulerService::class);

        // Schedule first session with vehicle from 16:00 to 17:00
        $schedule1 = $service->scheduleLesson([
            'branch_id' => $branch->id,
            'student_id' => $student1->id,
            'instructor_id' => $instructor1->id,
            'vehicle_id' => $vehicle->id,
            'scheduled_date' => now()->addDays(4)->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:00:00',
            'lesson_type' => 'practical',
        ]);

        $this->assertNotNull($schedule1->id);

        // Attempt overlapping session with SAME vehicle from 16:15 to 17:15
        $this->expectException(ValidationException::class);

        $service->scheduleLesson([
            'branch_id' => $branch->id,
            'student_id' => $student2->id,
            'instructor_id' => $instructor2->id,
            'vehicle_id' => $vehicle->id, // SAME VEHICLE
            'scheduled_date' => now()->addDays(4)->toDateString(),
            'start_time' => '16:15:00', // OVERLAPPING
            'end_time' => '17:15:00',
            'lesson_type' => 'practical',
        ]);
    }
}
