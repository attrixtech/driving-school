<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Expense;
use App\Models\Instructor;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Vehicle;
use App\Services\BillingService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request, BillingService $billingService)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        // Financial Metrics
        $fin = $billingService->getFinancialMetrics($branchId);

        // Course-wise Admissions
        $courseStats = Admission::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with('course')
            ->selectRaw('course_id, count(*) as count, sum(total_amount) as total_revenue')
            ->groupBy('course_id')
            ->get();

        // Expense Category Breakdown
        $expenseCategories = Expense::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('expense_category, sum(amount) as total')
            ->groupBy('expense_category')
            ->get();

        // Instructor Performance
        $instructors = Instructor::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->withCount(['lessonSchedules as completed_sessions' => fn($q) => $q->where('status', 'completed')])
            ->get();

        // Fleet Utilization
        $vehicles = Vehicle::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->withCount('lessonSchedules')
            ->withSum('fuelLogs as total_fuel_cost', 'total_cost')
            ->withSum('maintenances as total_maintenance_cost', 'cost')
            ->get();

        return view('reports.index', compact('fin', 'courseStats', 'expenseCategories', 'instructors', 'vehicles'));
    }
}
