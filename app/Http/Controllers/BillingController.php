<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Vehicle;
use App\Services\BillingService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function invoices(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;
        $status = $request->query('status');

        $invoices = Invoice::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->with(['student', 'admission.course', 'branch'])
            ->latest('invoice_date')
            ->paginate(15);

        return view('billing.invoices', compact('invoices', 'status'));
    }

    public function showInvoice(Invoice $invoice)
    {
        $invoice->load(['student', 'admission.course', 'admission.package', 'branch', 'tenant.company', 'payments']);
        return view('billing.invoice-show', compact('invoice'));
    }

    public function payments(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        $payments = Payment::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with(['student', 'invoice', 'receivedBy', 'branch'])
            ->latest('payment_date')
            ->paginate(15);

        $pendingInvoices = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->with(['student'])
            ->get();

        $students = Student::where('tenant_id', $tenantId)->get();

        return view('billing.payments', compact('payments', 'pendingInvoices', 'students'));
    }

    public function storePayment(Request $request, BillingService $billingService)
    {
        $validated = $request->validate([
            'invoice_id' => 'nullable|exists:invoices,id',
            'student_id' => 'required|exists:students,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|in:cash,upi,razorpay,card,bank_transfer,cheque',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $payment = $billingService->recordPayment($validated);

        return redirect()->back()->with('success', "Payment of ₹" . number_format($payment->amount, 2) . " recorded. Receipt #{$payment->receipt_number}");
    }

    public function expenses(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;
        $category = $request->query('category');

        $expenses = Expense::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($category, fn($q) => $q->where('expense_category', $category))
            ->with(['vehicle', 'createdBy', 'branch'])
            ->latest('expense_date')
            ->paginate(15);

        $vehicles = Vehicle::where('tenant_id', $tenantId)->get();

        return view('billing.expenses', compact('expenses', 'vehicles', 'category'));
    }

    public function storeExpense(Request $request, BillingService $billingService)
    {
        $validated = $request->validate([
            'expense_category' => 'required|string|max:50',
            'title' => 'required|string|max:150',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'payment_mode' => 'required|in:cash,upi,bank_transfer,cheque,card',
            'reference_number' => 'nullable|string|max:100',
            'paid_to' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
        ]);

        $expense = $billingService->recordExpense($validated);

        return redirect()->back()->with('success', "Expense of ₹" . number_format($expense->amount, 2) . " ({$expense->title}) recorded.");
    }
}
