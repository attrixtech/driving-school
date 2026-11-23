<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Admission;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingService
{
    /**
     * Records a fee payment against an invoice and admission.
     */
    public function recordPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
            $branchId = $data['branch_id'] ?? session('active_branch_id') ?? auth()->user()?->branch_id;

            $invoice = !empty($data['invoice_id']) ? Invoice::findOrFail($data['invoice_id']) : null;
            $admission = !empty($data['admission_id']) ? Admission::findOrFail($data['admission_id']) : $invoice?->admission;

            $amount = floatval($data['amount']);
            $receiptNumber = 'RCP-' . date('Y') . '-' . strtoupper(Str::random(6));

            $payment = Payment::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'student_id' => $data['student_id'] ?? $invoice?->student_id ?? $admission?->student_id,
                'admission_id' => $admission?->id,
                'invoice_id' => $invoice?->id,
                'received_by_user_id' => auth()->id(),
                'receipt_number' => $receiptNumber,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'amount' => $amount,
                'payment_mode' => $data['payment_mode'] ?? 'cash',
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            // Update Invoice balance if present
            if ($invoice) {
                $newPaid = $invoice->paid_amount + $amount;
                $newBalance = max(0, $invoice->total_amount - $newPaid);
                $status = $newBalance <= 0 ? 'paid' : 'partially_paid';

                $invoice->update([
                    'paid_amount' => $newPaid,
                    'balance_amount' => $newBalance,
                    'status' => $status,
                ]);
            }

            // Update Admission balance if present
            if ($admission) {
                $newAdmPaid = $admission->paid_amount + $amount;
                $newAdmBalance = max(0, $admission->total_amount - $newAdmPaid);
                $admStatus = $newAdmBalance <= 0 ? 'paid' : 'partially_paid';

                $admission->update([
                    'paid_amount' => $newAdmPaid,
                    'balance_amount' => $newAdmBalance,
                    'payment_status' => $admStatus,
                ]);
            }

            ActivityLog::record(
                'billing',
                'payment',
                "Recorded payment of ₹{$amount} with Receipt #{$receiptNumber}",
                ['payment_id' => $payment->id, 'amount' => $amount]
            );

            return $payment->load(['student', 'invoice', 'receivedBy']);
        });
    }

    /**
     * Records a business or vehicle expense.
     */
    public function recordExpense(array $data): Expense
    {
        return DB::transaction(function () use ($data) {
            $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
            $branchId = $data['branch_id'] ?? session('active_branch_id') ?? auth()->user()?->branch_id;

            $expense = Expense::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'created_by_user_id' => auth()->id(),
                'expense_category' => $data['expense_category'],
                'title' => $data['title'],
                'expense_date' => $data['expense_date'] ?? now()->toDateString(),
                'amount' => floatval($data['amount']),
                'payment_mode' => $data['payment_mode'] ?? 'cash',
                'reference_number' => $data['reference_number'] ?? null,
                'paid_to' => $data['paid_to'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            ActivityLog::record(
                'billing',
                'expense',
                "Recorded expense of ₹{$expense->amount} ({$expense->title})",
                ['expense_id' => $expense->id]
            );

            return $expense;
        });
    }

    /**
     * Calculates real financial metrics from the database.
     */
    public function getFinancialMetrics(?int $branchId = null): array
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;

        $paymentsQuery = Payment::where('tenant_id', $tenantId)->where('status', 'completed');
        $invoicesQuery = Invoice::where('tenant_id', $tenantId)->where('status', '!=', 'cancelled');
        $expensesQuery = Expense::where('tenant_id', $tenantId);

        if ($branchId) {
            $paymentsQuery->where('branch_id', $branchId);
            $invoicesQuery->where('branch_id', $branchId);
            $expensesQuery->where('branch_id', $branchId);
        }

        $totalCollected = (clone $paymentsQuery)->sum('amount');
        $todayCollected = (clone $paymentsQuery)->whereDate('payment_date', now()->toDateString())->sum('amount');
        $monthCollected = (clone $paymentsQuery)->whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year)->sum('amount');

        $totalOutstanding = (clone $invoicesQuery)->sum('balance_amount');
        $totalInvoiced = (clone $invoicesQuery)->sum('total_amount');

        $totalExpenses = (clone $expensesQuery)->sum('amount');
        $monthExpenses = (clone $expensesQuery)->whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('amount');

        $estimatedProfit = $monthCollected - $monthExpenses;

        return [
            'today_collection' => $todayCollected,
            'month_collection' => $monthCollected,
            'total_collection' => $totalCollected,
            'total_invoiced' => $totalInvoiced,
            'total_outstanding' => $totalOutstanding,
            'month_expenses' => $monthExpenses,
            'total_expenses' => $totalExpenses,
            'estimated_profit' => $estimatedProfit,
        ];
    }
}
