<?php

namespace Tests\Feature;

use App\Models\Admission;
use App\Models\Certificate;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use App\Services\BillingService;
use App\Services\ProgressTrackerService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingAndProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_payment_updates_invoice_and_admission_balances(): void
    {
        $owner = User::where('email', 'owner@apexdriving.com')->first();
        $this->actingAs($owner);

        $invoice = Invoice::where('status', 'partially_paid')->first();
        $this->assertNotNull($invoice);

        $initialBalance = $invoice->balance_amount;
        $payAmount = 1000.00;

        $billingService = app(BillingService::class);
        $payment = $billingService->recordPayment([
            'invoice_id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'admission_id' => $invoice->admission_id,
            'amount' => $payAmount,
            'payment_mode' => 'cash',
        ]);

        $this->assertNotNull($payment->id);
        $this->assertStringStartsWith('RCP-', $payment->receipt_number);

        $invoice->refresh();
        $this->assertEquals($initialBalance - $payAmount, $invoice->balance_amount);
    }

    public function test_mock_test_and_certificate_verification(): void
    {
        $owner = User::where('email', 'owner@apexdriving.com')->first();
        $this->actingAs($owner);

        $student = Student::first();
        $progressService = app(ProgressTrackerService::class);

        $mockTest = $progressService->recordMockTest([
            'student_id' => $student->id,
            'test_title' => 'RTO Traffic Signs Final Assessment',
            'category' => 'comprehensive_rto',
            'total_questions' => 20,
            'correct_answers' => 18,
            'passing_score' => 12,
        ]);

        $this->assertTrue($mockTest->is_passed);
        $this->assertEquals(90.00, $mockTest->score_percentage);

        // Test Certificate Generation
        $response = $this->post('/certificates/issue', [
            'student_id' => $student->id,
            'grade' => 95.00,
        ]);

        $cert = Certificate::where('student_id', $student->id)->latest()->first();
        $this->assertNotNull($cert);
        $this->assertStringStartsWith('CERT-', $cert->certificate_number);

        // Test Public QR Verification Page
        $verifyResponse = $this->get('/certificates/verify/' . $cert->verification_code);
        $verifyResponse->assertStatus(200);
        $verifyResponse->assertSee('Certificate Authenticated');
        $verifyResponse->assertSee($student->full_name);
    }
}
