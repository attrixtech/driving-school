<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AdmissionService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $owner;
    protected Student $student1;
    protected Student $student2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->owner = User::where('email', 'owner@apexdriving.com')->first();
        $this->student1 = Student::first();
        $this->student2 = Student::skip(1)->first();
    }

    public function test_student_portal_displays_student_switcher_dropdown(): void
    {
        $response = $this->actingAs($this->owner)->get('/student/portal');

        $response->assertStatus(200);
        $response->assertSeeText('Student Learner Portal');
        $response->assertSeeText($this->student1->full_name);
        // Verify dropdown contains other students
        $response->assertSeeText($this->student2->full_name);
    }

    public function test_student_portal_switches_student_profile_via_query_param(): void
    {
        $response = $this->actingAs($this->owner)->get("/student/portal?student_id={$this->student2->id}");

        $response->assertStatus(200);
        $response->assertSeeText($this->student2->full_name);
        $response->assertSeeText($this->student2->student_code);
    }
}
