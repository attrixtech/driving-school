<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issued_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('certificate_number', 50);
            $table->string('verification_code', 64)->unique(); // for QR verification
            $table->date('issue_date');
            $table->string('course_name');
            $table->string('vehicle_category_code', 30);
            $table->decimal('practical_hours_completed', 5, 2);
            $table->decimal('theory_hours_completed', 5, 2);
            $table->decimal('final_grade', 4, 2)->default(90.00);
            $table->enum('status', ['issued', 'revoked'])->default('issued');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'certificate_number']);
            $table->index(['tenant_id', 'student_id']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module', 50); // auth, admission, scheduling, billing, fleet, student
            $table->string('action', 50); // create, update, delete, login, payment, status_change
            $table->text('description');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type', 50); // alert, reminder, payment, schedule, system
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('certificates');
    }
};
