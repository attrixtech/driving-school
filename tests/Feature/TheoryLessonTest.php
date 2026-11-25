<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\StudentProgress;
use App\Models\Tenant;
use App\Models\TheoryClass;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TheoryLessonTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected User $owner;
    protected Student $student;
    protected Instructor $instructor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->owner = User::where('email', 'owner@thrumble.driving-school')->first();
        $this->student = Student::first();
        $this->instructor = Instructor::first();
        $this->branch = Branch::first();
        $this->tenant = Tenant::first();
    }

    public function test_user_can_access_traffic_signs_and_theory_learning_hub(): void
    {
        $response = $this->actingAs($this->owner)->get('/training/theory');

        $response->assertStatus(200);
        $response->assertSeeText('Traffic Signs & Theory Learning Center');
        $response->assertSeeText('Stop Sign');
        $response->assertSeeText('Give Way / Yield');
        $response->assertSeeText('Speed Limit (50 km/h)');
        $response->assertSeeText('Cockpit Drill & Vehicle Anatomy');
    }

    public function test_traffic_signs_can_be_filtered_by_category(): void
    {
        // Filter by cautionary signs
        $response = $this->actingAs($this->owner)->get('/training/theory?category=cautionary');

        $response->assertStatus(200);
        $response->assertSeeText('Right Hand Curve Ahead');
        $response->assertSeeText('Pedestrian Crossing Ahead');
        $response->assertDontSeeText('Stop Sign');
    }

    public function test_staff_can_schedule_live_theory_classroom_batch(): void
    {
        $classData = [
            'branch_id' => $this->branch->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Emergency Braking & First Aid Protocol',
            'description' => 'Covering Section 134A Good Samaritan Law and CPR basics.',
            'session_date' => now()->addDays(2)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:30',
            'max_seats' => 25,
            'room_or_link' => 'Classroom 101',
            'status' => 'scheduled',
        ];

        $response = $this->actingAs($this->owner)->post('/training/theory/classes', $classData);

        $response->assertRedirect('/training/theory?tab=classes');
        $this->assertDatabaseHas('theory_classes', [
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'title' => 'Emergency Braking & First Aid Protocol',
            'room_or_link' => 'Classroom 101',
        ]);
    }

    public function test_recording_theory_attendance_updates_student_progress(): void
    {
        $theoryClass = TheoryClass::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'instructor_id' => $this->instructor->id,
            'title' => 'Defensive Driving & Roundabouts',
            'session_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'max_seats' => 20,
            'status' => 'ongoing',
        ]);

        $initialHours = (float)($this->student->progress?->completed_theory_hours ?? 0);

        $response = $this->actingAs($this->owner)->post("/training/theory/classes/{$theoryClass->id}/attend", [
            'student_id' => $this->student->id,
            'status' => 'present',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('theory_attendances', [
            'tenant_id' => $this->tenant->id,
            'theory_class_id' => $theoryClass->id,
            'student_id' => $this->student->id,
            'status' => 'present',
        ]);

        $progress = StudentProgress::where('student_id', $this->student->id)->first();
        $this->assertGreaterThan($initialHours, (float)$progress->completed_theory_hours);
    }
}
