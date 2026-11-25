<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Admission;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdmissionService
{
    public function createAdmission(array $data): Admission
    {
        return DB::transaction(function () use ($data) {
            $tenantId = $data['tenant_id'] ?? session('active_tenant_id') ?? auth()->user()?->tenant_id;
            $branchId = $data['branch_id'] ?? session('active_branch_id') ?? auth()->user()?->branch_id;

            // 1. Student setup or find
            if (!empty($data['student_id'])) {
                $student = Student::findOrFail($data['student_id']);
            } else {
                $studentCode = 'STU-' . date('Y') . '-' . strtoupper(Str::random(5));
                $student = Student::create([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'student_code' => $studentCode,
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'] ?? null,
                    'phone' => $data['phone'],
                    'alternate_phone' => $data['alternate_phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'gender' => $data['gender'] ?? 'male',
                    'blood_group' => $data['blood_group'] ?? null,
                    'address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? 'Agra',
                    'state' => $data['state'] ?? 'Uttar Pradesh',
                    'postal_code' => $data['postal_code'] ?? null,
                    'guardian_name' => $data['guardian_name'] ?? null,
                    'guardian_phone' => $data['guardian_phone'] ?? null,
                    'learner_license_number' => $data['learner_license_number'] ?? null,
                    'll_issue_date' => $data['ll_issue_date'] ?? null,
                    'll_expiry_date' => $data['ll_expiry_date'] ?? null,
                    'dl_status' => !empty($data['learner_license_number']) ? 'll_issued' : 'not_applied',
                    'status' => 'enrolled',
                ]);
            }

            // 2. Fetch Course and Package
            $course = Course::findOrFail($data['course_id']);
            $package = !empty($data['course_package_id']) ? CoursePackage::find($data['course_package_id']) : null;

            $baseFee = $package ? $package->package_fee : $course->default_fee;
            $discount = floatval($data['discount_amount'] ?? 0);
            $subtotal = max(0, $baseFee - $discount);
            $taxRate = 18.0; // 18% GST
            $taxAmount = round(($subtotal * $taxRate) / 100, 2);
            $totalAmount = $subtotal + $taxAmount;

            $admissionNumber = 'ADM-' . date('Y') . '-' . strtoupper(Str::random(5));

            // 3. Create Admission Record
            $admission = Admission::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'student_id' => $student->id,
                'course_id' => $course->id,
                'course_package_id' => $package?->id,
                'instructor_id' => $data['instructor_id'] ?? null,
                'admitted_by_user_id' => auth()->id(),
                'admission_number' => $admissionNumber,
                'admission_date' => $data['admission_date'] ?? now()->toDateString(),
                'training_start_date' => $data['training_start_date'] ?? now()->toDateString(),
                'expected_completion_date' => $data['expected_completion_date'] ?? now()->addDays($package?->duration_days ?? $course->duration_days ?? 30)->toDateString(),
                'course_fee' => $baseFee,
                'discount_amount' => $discount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0.00,
                'balance_amount' => $totalAmount,
                'payment_status' => 'unpaid',
                'status' => 'active',
                'remarks' => $data['remarks'] ?? null,
            ]);

            // 4. Generate Invoice
            $invoiceNumber = 'INV-' . date('Y') . '-' . strtoupper(Str::random(6));
            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'student_id' => $student->id,
                'admission_id' => $admission->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(7)->toDateString(),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0.00,
                'balance_amount' => $totalAmount,
                'status' => 'unpaid',
                'notes' => 'Admission Invoice for ' . $course->name . ($package ? ' - ' . $package->name : ''),
            ]);

            // 5. Initial Payment processing if provided
            $initialPaid = floatval($data['initial_payment_amount'] ?? 0);
            if ($initialPaid > 0) {
                $receiptNumber = 'RCP-' . date('Y') . '-' . strtoupper(Str::random(6));
                $payment = Payment::create([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'student_id' => $student->id,
                    'admission_id' => $admission->id,
                    'invoice_id' => $invoice->id,
                    'received_by_user_id' => auth()->id(),
                    'receipt_number' => $receiptNumber,
                    'payment_date' => now()->toDateString(),
                    'amount' => $initialPaid,
                    'payment_mode' => $data['payment_mode'] ?? 'cash',
                    'transaction_reference' => $data['transaction_reference'] ?? null,
                    'status' => 'completed',
                    'notes' => 'Admission initial fee payment',
                ]);

                $invBalance = max(0, $totalAmount - $initialPaid);
                $invStatus = $invBalance <= 0 ? 'paid' : 'partially_paid';

                $invoice->update([
                    'paid_amount' => $initialPaid,
                    'balance_amount' => $invBalance,
                    'status' => $invStatus,
                ]);

                $admission->update([
                    'paid_amount' => $initialPaid,
                    'balance_amount' => $invBalance,
                    'payment_status' => $invStatus,
                ]);
            }

            // 6. Initialize Student Progress
            $practicalHours = $package ? $package->practical_sessions_count : $course->total_practical_hours;
            $theoryHours = $package ? $package->theory_sessions_count : $course->total_theory_hours;

            StudentProgress::create([
                'tenant_id' => $tenantId,
                'student_id' => $student->id,
                'admission_id' => $admission->id,
                'required_practical_hours' => $practicalHours,
                'completed_practical_hours' => 0.00,
                'required_theory_hours' => $theoryHours,
                'completed_theory_hours' => 0.00,
                'mock_tests_passed_count' => 0,
                'overall_progress_percentage' => 0.00,
                'eligible_for_dl_test' => false,
                'course_completed' => false,
            ]);

            ActivityLog::record(
                'admission',
                'create',
                "Student {$student->full_name} admitted for {$course->name} (Admission #{$admission->admission_number})",
                ['admission_id' => $admission->id, 'student_id' => $student->id]
            );

            return $admission->load(['student', 'course', 'package', 'invoices', 'payments']);
        });
    }
}
