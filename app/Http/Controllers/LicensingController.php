<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class LicensingController extends Controller
{
    public function records(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;
        $dlStatus = $request->query('dl_status');

        $students = Student::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($dlStatus, fn($q) => $q->where('dl_status', $dlStatus))
            ->with(['branch', 'progress'])
            ->latest()
            ->paginate(20);

        return view('licensing.records', compact('students', 'dlStatus'));
    }
}
