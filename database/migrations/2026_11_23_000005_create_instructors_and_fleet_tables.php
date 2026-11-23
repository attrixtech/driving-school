<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('instructor_code', 30);
            $table->string('name');
            $table->string('phone', 25);
            $table->string('email')->nullable();
            $table->string('license_number', 50);
            $table->date('license_expiry_date')->nullable();
            $table->unsignedSmallInteger('experience_years')->default(1);
            $table->json('vehicle_types')->nullable(); // ['LMV', 'MCWG']
            $table->enum('status', ['active', 'on_leave', 'inactive'])->default('active');
            $table->decimal('hourly_rate', 8, 2)->default(0.00);
            $table->decimal('monthly_salary', 10, 2)->default(0.00);
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->text('address')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'instructor_code']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('instructor_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0 = Sunday, 1 = Monday ... 6 = Saturday
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'instructor_id', 'day_of_week'], 'inst_avail_idx');
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('registration_number', 30);
            $table->string('make', 50);
            $table->string('model', 50);
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('color', 30)->nullable();
            $table->enum('transmission', ['manual', 'automatic'])->default('manual');
            $table->enum('fuel_type', ['petrol', 'diesel', 'cng', 'electric', 'hybrid'])->default('petrol');
            $table->enum('dual_control_fitted', ['yes', 'no'])->default('yes');
            $table->enum('status', ['active', 'in_maintenance', 'breakdown', 'retired'])->default('active');
            $table->unsignedInteger('odometer_reading')->default(0);
            $table->date('insurance_expiry_date')->nullable();
            $table->date('fitness_expiry_date')->nullable();
            $table->date('pollution_expiry_date')->nullable();
            $table->date('permit_expiry_date')->nullable();
            $table->date('road_tax_expiry_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'registration_number']);
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('vehicle_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 50); // rc, insurance, fitness, puc, permit, tax_token
            $table->string('document_number', 50)->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('file_path')->nullable();
            $table->string('issuing_authority')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'vehicle_id', 'expiry_date']);
        });

        Schema::create('vehicle_fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->date('fuel_date');
            $table->decimal('liters', 8, 2);
            $table->decimal('cost_per_liter', 8, 2);
            $table->decimal('total_cost', 10, 2);
            $table->unsignedInteger('odometer_reading');
            $table->string('station_name')->nullable();
            $table->string('receipt_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'vehicle_id', 'fuel_date']);
        });

        Schema::create('vehicle_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->string('service_type', 100); // routine_service, brake_overhaul, clutch_replacement, tire_change, oil_change, repair
            $table->date('service_date');
            $table->unsignedInteger('odometer_reading')->nullable();
            $table->decimal('cost', 10, 2)->default(0.00);
            $table->string('workshop_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('next_service_due_date')->nullable();
            $table->unsignedInteger('next_service_due_km')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'vehicle_id', 'service_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenances');
        Schema::dropIfExists('vehicle_fuel_logs');
        Schema::dropIfExists('vehicle_documents');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('instructor_availabilities');
        Schema::dropIfExists('instructors');
    }
};
