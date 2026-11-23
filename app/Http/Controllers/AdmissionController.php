<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\Instructor;
use App\Models\Lead;
use App\Models\Student;
use App\Services\AdmissionService;
use Illuminate\Http\Request;

class AdmissionController extends Controller
{
    public function create(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        $lead = null;
        if ($request->has('lead_id')) {
            $lead = Lead::where('tenant_id', $tenantId)->find($request->query('lead_id'));
        }

        $branches = Branch::where('tenant_id', $tenantId)->where('is_active', true)->get();
        $courses = Course::where('tenant_id', $tenantId)->where('is_active', true)->with('packages')->get();
        $packages = CoursePackage::where('tenant_id', $tenantId)->where('is_active', true)->get();
        $instructors = Instructor::where('tenant_id', $tenantId)->where('status', 'active')->get();

        return view('admissions.create', compact('branches', 'courses', 'packages', 'instructors', 'lead', 'branchId'));
    }

    public function store(Request $request, AdmissionService $admissionService)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'phone' => 'required|string|max:25',
            'alternate_phone' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:150',
            'date_of_birth' => 'nullable|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'guardian_name' => 'nullable|string|max:150',
            'guardian_phone' => 'nullable|string|max:25',
            'learner_license_number' => 'nullable|string|max:50',
            'll_issue_date' => 'nullable|date',
            'll_expiry_date' => 'nullable|date',
            'course_id' => 'required|exists:courses,id',
            'course_package_id' => 'nullable|exists:course_packages,id',
            'instructor_id' => 'nullable|exists:instructors,id',
            'admission_date' => 'required|date',
            'training_start_date' => 'nullable|date',
            'discount_amount' => 'nullable|numeric|min:0',
            'initial_payment_amount' => 'nullable|numeric|min:0',
            'payment_mode' => 'nullable|in:cash,upi,razorpay,card,bank_transfer',
            'transaction_reference' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
            'lead_id' => 'nullable|exists:leads,id',
        ]);

        $admission = $admissionService->createAdmission($validated);

        if (!empty($validated['lead_id'])) {
            Lead::where('id', $validated['lead_id'])->update(['status' => 'admitted']);
        }

        return redirect()->route('admissions.show', $admission->id)
            ->with('success', "Student {$admission->student->full_name} admitted successfully! Admission No: {$admission->admission_number}");
    }

    public function show(Admission $admission)
    {
        $admission->load(['student.documents', 'course', 'package', 'instructor', 'invoices.payments', 'progress']);
        return view('admissions.show', compact('admission'));
    }

    public function print(Admission $admission)
    {
        $admission->load(['student', 'course', 'package', 'instructor', 'branch', 'tenant.company']);
        return view('admissions.print', compact('admission'));
    }
}
