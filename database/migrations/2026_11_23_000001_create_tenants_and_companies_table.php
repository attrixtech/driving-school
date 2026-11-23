<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->string('contact_person')->nullable();
            $table->string('email')->unique();
            $table->string('phone', 25)->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended', 'trial'])->default('active');
            $table->string('subscription_plan')->default('standard'); // standard, premium, enterprise
            $table->enum('subscription_status', ['trial', 'active', 'past_due', 'cancelled'])->default('trial');
            $table->date('subscription_ends_at')->nullable();
            $table->unsignedInteger('max_branches')->default(2);
            $table->unsignedInteger('max_students')->default(500);
            $table->unsignedInteger('max_vehicles')->default(15);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('legal_name');
            $table->string('trade_name')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('gstin', 20)->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('India');
            $table->string('currency', 10)->default('INR');
            $table->string('currency_symbol', 10)->default('₹');
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
        Schema::dropIfExists('tenants');
    }
};
