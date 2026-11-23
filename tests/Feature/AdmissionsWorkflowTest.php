<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\Invoice;
use App\Models\StudentProgress;
use App\Models\User;
use App\Services\AdmissionService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admission_service_creates_admission_invoice_and_progress(): void
    {
        $owner = User::where('email', 'owner@apexdriving.com')->first();
        $this->actingAs($owner);

        $branch = Branch::first();
        $course = Course::first();
        $package = CoursePackage::where('course_id', $course->id)->first();

        $service = app(AdmissionService::class);
        $admission = $service->createAdmission([
            'branch_id' => $branch->id,
            'first_name' => 'Kunal',
            'last_name' => 'Khurana',
            'phone' => '+91 99887 66554',
            'gender' => 'male',
            'course_id' => $course->id,
            'course_package_id' => $package?->id,
            'discount_amount' => 500.00,
            'initial_payment_amount' => 3000.00,
            'payment_mode' => 'upi',
            'admission_date' => now()->toDateString(),
        ]);

        $this->assertNotNull($admission->id);
        $this->assertStringStartsWith('ADM-', $admission->admission_number);

        // Verify Student was created
        $student = $admission->student;
        $this->assertEquals('Kunal', $student->first_name);
        $this->assertStringStartsWith('STU-', $student->student_code);

        // Verify Invoice was created with 18% GST
        $invoice = Invoice::where('admission_id', $admission->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(18.0, $invoice->tax_rate);
        $this->assertEquals(3000.00, $invoice->paid_amount);
        $this->assertGreaterThan(0, $invoice->balance_amount);
        $this->assertEquals('partially_paid', $invoice->status);

        // Verify Student Progress was initialized
        $progress = StudentProgress::where('student_id', $student->id)->first();
        $this->assertNotNull($progress);
        $this->assertGreaterThan(0, $progress->required_practical_hours);
        $this->assertEquals(0, $progress->completed_practical_hours);
    }
}
