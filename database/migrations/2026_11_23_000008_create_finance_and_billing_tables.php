<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admission_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number', 40);
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(18.00); // e.g. 18% GST (9% CGST + 9% SGST)
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('balance_amount', 10, 2)->default(0.00);
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'cancelled'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'invoice_number']);
            $table->index(['tenant_id', 'status', 'invoice_date']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admission_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('received_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('receipt_number', 40);
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_mode', ['cash', 'upi', 'razorpay', 'card', 'bank_transfer', 'cheque'])->default('cash');
            $table->string('transaction_reference', 100)->nullable();
            $table->enum('status', ['completed', 'pending', 'failed', 'refunded'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'receipt_number']);
            $table->index(['tenant_id', 'payment_date', 'status']);
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('expense_category', 50); // fuel, maintenance, rent, electricity, salary, rto_fee, marketing, office_supplies, other
            $table->string('title');
            $table->date('expense_date');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_mode', ['cash', 'upi', 'bank_transfer', 'cheque', 'card'])->default('cash');
            $table->string('reference_number', 100)->nullable();
            $table->string('paid_to')->nullable();
            $table->string('receipt_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'expense_date', 'expense_category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
    }
};
