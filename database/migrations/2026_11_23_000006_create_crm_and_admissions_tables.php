<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->string('lead_number', 30);
            $table->string('full_name');
            $table->string('phone', 25);
            $table->string('email')->nullable();
            $table->string('city', 100)->nullable();
            $table->enum('source', ['walkin', 'website', 'phone', 'referral', 'social_media', 'justdial', 'other'])->default('walkin');
            $table->enum('status', ['new', 'contacted', 'interested', 'trial_booked', 'admitted', 'lost'])->default('new');
            $table->date('follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'lead_number']);
            $table->index(['tenant_id', 'status', 'follow_up_date']);
        });

        Schema::create('lead_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('followup_at');
            $table->enum('communication_channel', ['call', 'whatsapp', 'email', 'in_person', 'sms'])->default('call');
            $table->enum('outcome', ['connected', 'no_answer', 'callback_requested', 'not_interested', 'enrolled'])->default('connected');
            $table->text('notes');
            $table->date('next_followup_date')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'lead_id']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // for student portal login
            $table->string('student_code', 30);
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('phone', 25);
            $table->string('alternate_phone', 25)->nullable();
            $table->string('email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 25)->nullable();
            $table->string('guardian_relation', 50)->nullable();

            // License tracking
            $table->string('learner_license_number', 50)->nullable();
            $table->date('ll_issue_date')->nullable();
            $table->date('ll_expiry_date')->nullable();
            $table->string('permanent_license_number', 50)->nullable();
            $table->date('dl_test_date')->nullable();
            $table->string('dl_rto_office', 100)->nullable();
            $table->enum('dl_status', ['not_applied', 'll_applied', 'll_issued', 'dl_applied', 'dl_test_scheduled', 'dl_passed', 'dl_issued'])->default('not_applied');

            $table->enum('status', ['enrolled', 'in_training', 'training_completed', 'license_in_process', 'completed', 'dropped'])->default('enrolled');
            $table->string('photo_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'student_code']);
            $table->index(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained()->nullOnDelete(); // preferred or assigned instructor
            $table->foreignId('admitted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('admission_number', 30);
            $table->date('admission_date');
            $table->date('training_start_date')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();

            // Fees
            $table->decimal('course_fee', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('balance_amount', 10, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid');

            $table->enum('status', ['active', 'completed', 'cancelled', 'on_hold'])->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'admission_number']);
            $table->index(['tenant_id', 'student_id']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 50); // aadhaar, pan, passport, voter_id, ll_copy, medical_certificate, photo
            $table->string('document_number', 50)->nullable();
            $table->string('file_path');
            $table->string('original_filename')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('admissions');
        Schema::dropIfExists('students');
        Schema::dropIfExists('lead_followups');
        Schema::dropIfExists('leads');
    }
};
