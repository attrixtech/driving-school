<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admission_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('lesson_type', ['practical', 'theory', 'simulator', 'assessment', 'mock_test'])->default('practical');
            $table->string('topic')->nullable();
            $table->date('scheduled_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'missed', 'cancelled', 'rescheduled'])->default('scheduled');
            $table->text('cancellation_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for conflict detection and fast querying
            $table->index(['tenant_id', 'instructor_id', 'scheduled_date', 'start_time', 'end_time'], 'idx_sch_inst_slot');
            $table->index(['tenant_id', 'vehicle_id', 'scheduled_date', 'start_time', 'end_time'], 'idx_sch_veh_slot');
            $table->index(['tenant_id', 'student_id', 'scheduled_date', 'start_time', 'end_time'], 'idx_sch_stu_slot');
            $table->index(['tenant_id', 'scheduled_date', 'status'], 'idx_sch_date_status');
        });

        Schema::create('lesson_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->time('actual_start_time')->nullable();
            $table->time('actual_end_time')->nullable();
            $table->unsignedInteger('start_odometer')->nullable();
            $table->unsignedInteger('end_odometer')->nullable();
            $table->decimal('kilometers_driven', 6, 2)->default(0.00);
            $table->unsignedSmallInteger('student_rating')->nullable(); // 1 to 5
            $table->text('student_feedback')->nullable();
            $table->text('instructor_notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'student_id']);
        });

        Schema::create('practical_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->date('assessment_date');

            // Practical parameters (scored 1 to 5, or 1 to 10)
            $table->unsignedTinyInteger('cockpit_drill')->default(3); // mirrors, seatbelt, ergonomics
            $table->unsignedTinyInteger('clutch_and_gears')->default(3);
            $table->unsignedTinyInteger('steering_control')->default(3);
            $table->unsignedTinyInteger('speed_and_braking')->default(3);
            $table->unsignedTinyInteger('traffic_signals_and_signs')->default(3);
            $table->unsignedTinyInteger('turning_and_roundabouts')->default(3);
            $table->unsignedTinyInteger('reverse_and_parallel_parking')->default(3);
            $table->unsignedTinyInteger('slope_stop_and_start')->default(3);
            $table->unsignedTinyInteger('overall_score')->default(7); // out of 10
            $table->boolean('is_ready_for_dl_test')->default(false);
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('instructor_remarks')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'student_id']);
        });

        Schema::create('theory_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title'); // Traffic Signs, RTO Rules, Safe Driving, First Aid
            $table->text('description')->nullable();
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('max_seats')->default(30);
            $table->string('room_or_link')->nullable();
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();

            $table->index(['tenant_id', 'session_date']);
        });

        Schema::create('theory_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('theory_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['present', 'absent'])->default('present');
            $table->timestamps();

            $table->unique(['theory_class_id', 'student_id']);
        });

        Schema::create('mock_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('test_title');
            $table->enum('category', ['road_signs', 'traffic_regulations', 'vehicle_controls', 'comprehensive_rto'])->default('comprehensive_rto');
            $table->unsignedSmallInteger('total_questions')->default(20);
            $table->unsignedSmallInteger('correct_answers')->default(0);
            $table->unsignedSmallInteger('passing_score')->default(12);
            $table->decimal('score_percentage', 5, 2)->default(0.00);
            $table->boolean('is_passed')->default(false);
            $table->unsignedSmallInteger('time_taken_seconds')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'student_id']);
        });

        Schema::create('student_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admission_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('required_practical_hours')->default(15);
            $table->decimal('completed_practical_hours', 5, 2)->default(0.00);
            $table->unsignedSmallInteger('required_theory_hours')->default(5);
            $table->decimal('completed_theory_hours', 5, 2)->default(0.00);
            $table->unsignedSmallInteger('mock_tests_passed_count')->default(0);
            $table->decimal('overall_progress_percentage', 5, 2)->default(0.00);
            $table->boolean('eligible_for_dl_test')->default(false);
            $table->boolean('course_completed')->default(false);
            $table->date('completion_date')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'student_id', 'admission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_progress');
        Schema::dropIfExists('mock_tests');
        Schema::dropIfExists('theory_attendances');
        Schema::dropIfExists('theory_classes');
        Schema::dropIfExists('practical_assessments');
        Schema::dropIfExists('lesson_attendances');
        Schema::dropIfExists('lesson_schedules');
    }
};
