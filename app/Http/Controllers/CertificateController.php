<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Admission;
use App\Models\Certificate;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        $certificates = Certificate::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with(['student', 'branch', 'issuedBy'])
            ->latest()
            ->paginate(15);

        // Eligible students for new certificate issuance (who have active admissions)
        $eligibleStudents = Student::where('tenant_id', $tenantId)
            ->whereHas('progress', fn($q) => $q->where('eligible_for_dl_test', true))
            ->with(['latestAdmission.course'])
            ->get();

        return view('certificates.index', compact('certificates', 'eligibleStudents'));
    }

    public function issue(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'grade' => 'nullable|numeric|min:50|max:100',
        ]);

        $student = Student::with(['latestAdmission.course', 'progress'])->findOrFail($validated['student_id']);
        $admission = $student->latestAdmission;

        $certNumber = 'CERT-' . date('Y') . '-' . strtoupper(Str::random(6));
        $verificationCode = Str::random(32);

        $cert = Certificate::create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId ?? $student->branch_id,
            'student_id' => $student->id,
            'admission_id' => $admission->id,
            'issued_by_user_id' => auth()->id(),
            'certificate_number' => $certNumber,
            'verification_code' => $verificationCode,
            'issue_date' => now()->toDateString(),
            'course_name' => $admission->course?->name ?? 'Driving Training Course',
            'vehicle_category_code' => $admission->course?->category?->code ?? 'LMV',
            'practical_hours_completed' => $student->progress?->completed_practical_hours ?? 15,
            'theory_hours_completed' => $student->progress?->completed_theory_hours ?? 5,
            'final_grade' => $validated['grade'] ?? 92.00,
            'status' => 'issued',
            'remarks' => 'Successfully satisfied all practical driving and RTO theory competency criteria.',
        ]);

        $student->update(['status' => 'training_completed']);

        ActivityLog::record('certificates', 'issue', "Issued completion certificate {$certNumber} to {$student->full_name}", ['certificate_id' => $cert->id]);

        return redirect()->route('certificates.show', $cert->id)->with('success', "Certificate {$certNumber} generated successfully.");
    }

    public function show(Certificate $certificate)
    {
        $certificate->load(['student', 'branch', 'tenant.company', 'issuedBy']);
        return view('certificates.show', compact('certificate'));
    }

    public function verify(string $code)
    {
        $certificate = Certificate::withoutGlobalScopes()
            ->where('verification_code', $code)
            ->with(['student', 'branch', 'tenant.company'])
            ->firstOrFail();

        return view('certificates.verify', compact('certificate'));
    }
}
