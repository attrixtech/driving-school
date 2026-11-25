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
        $owner = User::where('email', 'owner@thrumble.driving-school')->first();
        $this->actingAs($owner);

        $branch = Branch::first();
        $course = Course::first();
        $package = CoursePackage::where('course_id', $course->id)->first();

        $service = app(AdmissionService::class);
        $admission = $service->createAdmission([
            'branch_id' => $branch->id,
            'first_name' => 'Kunal',
            'last_name' => 'Khurana',
            'phone' => '+91 98378 66554',
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

    public function test_crm_leads_page_displays_call_logs_and_followup_history(): void
    {
        $owner = User::where('email', 'owner@thrumble.driving-school')->first();
        $this->actingAs($owner);

        $response = $this->withSession([
            'active_tenant_id' => $owner->tenant_id,
            'active_branch_id' => $owner->branch_id,
        ])->get('/crm/leads');

        $response->assertStatus(200);
        $response->assertSee('Enquiries');
        $response->assertSee('Total Call Logs');
        $response->assertSee('Call Logs & Interaction History', false);
        $response->assertSee('Explained course fee structure');
        $response->assertSee('Phone Call');
    }

    public function test_can_log_followup_and_retrieve_call_logs_api(): void
    {
        $owner = User::where('email', 'owner@thrumble.driving-school')->first();
        $this->actingAs($owner);

        $lead = \App\Models\Lead::first();
        $this->assertNotNull($lead);

        $postResponse = $this->withSession([
            'active_tenant_id' => $owner->tenant_id,
            'active_branch_id' => $owner->branch_id,
        ])->post("/crm/leads/{$lead->id}/followup", [
            'communication_channel' => 'call',
            'outcome' => 'connected',
            'notes' => 'Customer confirmed joining date for four-wheeler driving course next Monday.',
            'next_followup_date' => now()->addDays(3)->toDateString(),
        ]);

        $postResponse->assertRedirect();
        $this->assertDatabaseHas('lead_followups', [
            'lead_id' => $lead->id,
            'notes' => 'Customer confirmed joining date for four-wheeler driving course next Monday.',
        ]);

        $apiResponse = $this->get("/crm/leads/{$lead->id}/logs");
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonFragment([
            'notes' => 'Customer confirmed joining date for four-wheeler driving course next Monday.',
        ]);
    }
}
