<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Two Wheeler (MCWG), Four Wheeler (LMV), Commercial (HMV), Automatic Car, etc.
            $table->string('code', 20); // MCWG, LMV, HMV, AUTO
            $table->string('license_class', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30);
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('transmission_type', ['manual', 'automatic', 'both'])->default('manual');
            $table->unsignedInteger('duration_days')->default(30);
            $table->unsignedInteger('total_practical_hours')->default(15);
            $table->unsignedInteger('total_theory_hours')->default(5);
            $table->decimal('default_fee', 10, 2)->default(0.00);
            $table->boolean('has_simulator')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('course_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30);
            $table->string('name'); // Beginner, Refresher, Crash Course, VIP
            $table->text('description')->nullable();
            $table->unsignedInteger('duration_days')->default(30);
            $table->unsignedInteger('practical_sessions_count')->default(15);
            $table->unsignedInteger('theory_sessions_count')->default(5);
            $table->decimal('package_fee', 10, 2)->default(0.00);
            $table->boolean('includes_ll_assistance')->default(true);
            $table->boolean('includes_dl_test_assistance')->default(true);
            $table->boolean('includes_doorstep_pickup')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_packages');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('vehicle_categories');
    }
};
