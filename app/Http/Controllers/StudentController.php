<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Course;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Services\ProgressTrackerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;
        $search = $request->query('search');
        $status = $request->query('status');

        $students = Student::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('student_code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('learner_license_number', 'like', "%{$search}%");
                });
            })
            ->with(['branch', 'latestAdmission.course', 'progress'])
            ->latest()
            ->paginate(15);

        $branches = Branch::where('tenant_id', $tenantId)->get();

        return view('students.index', compact('students', 'branches', 'search', 'status'));
    }

    public function show(Student $student)
    {
        $student->load([
            'branch',
            'admissions.course',
            'admissions.package',
            'admissions.instructor',
            'admissions.invoices.payments',
            'progress',
            'documents',
            'lessonSchedules.instructor',
            'lessonSchedules.vehicle',
            'lessonSchedules.attendance',
            'mockTests',
            'certificates',
        ]);

        return view('students.show', compact('student'));
    }

    public function uploadDocument(Request $request, Student $student)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|max:50',
            'document_number' => 'nullable|string|max:50',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'remarks' => 'nullable|string',
        ]);

        $file = $request->file('document_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('student_documents/' . $student->id, $filename, 'public');

        StudentDocument::create([
            'tenant_id' => $student->tenant_id,
            'student_id' => $student->id,
            'document_type' => $validated['document_type'],
            'document_number' => $validated['document_number'] ?? null,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'verification_status' => 'pending',
            'remarks' => $validated['remarks'] ?? null,
        ]);

        ActivityLog::record('student', 'document_upload', "Uploaded {$validated['document_type']} for {$student->full_name}", ['student_id' => $student->id]);

        return redirect()->back()->with('success', 'Document uploaded successfully and marked for verification.');
    }

    public function verifyDocument(Request $request, StudentDocument $document)
    {
        $status = $request->input('status', 'verified');
        $document->update([
            'verification_status' => $status,
            'remarks' => $request->input('remarks', $status === 'verified' ? 'Verified by staff' : 'Rejected'),
        ]);

        return redirect()->back()->with('success', "Document status updated to {$status}.");
    }

    public function updateLicense(Request $request, Student $student, ProgressTrackerService $progressService)
    {
        $validated = $request->validate([
            'learner_license_number' => 'nullable|string|max:50',
            'll_issue_date' => 'nullable|date',
            'll_expiry_date' => 'nullable|date',
            'permanent_license_number' => 'nullable|string|max:50',
            'dl_test_date' => 'nullable|date',
            'dl_rto_office' => 'nullable|string|max:100',
            'dl_status' => 'required|in:not_applied,ll_applied,ll_issued,dl_applied,dl_test_scheduled,dl_passed,dl_issued',
        ]);

        $progressService->updateLicenseDetails($student, $validated);

        return redirect()->back()->with('success', 'License details updated.');
    }

    public function myTraining(Request $request)
    {
        $user = auth()->user();
        $tenantId = session('active_tenant_id') ?? $user?->tenant_id;
        $requestedStudentId = $request->query('student_id', $request->query('preview_id'));

        // All students for switcher dropdown
        $allStudents = Student::where('tenant_id', $tenantId)
            ->with(['branch', 'latestAdmission.course'])
            ->orderBy('first_name')
            ->get();

        $student = null;
        if ($requestedStudentId) {
            $student = Student::where('tenant_id', $tenantId)->find($requestedStudentId);
        }

        // If not explicitly requested and user is a student, use their profile
        if (!$student && $user && $user->role_slug === 'student') {
            $student = Student::where('tenant_id', $tenantId)->where('user_id', $user->id)->first();
        }

        // Fallback to first available student
        if (!$student) {
            $student = $allStudents->first();
        }

        if (!$student) {
            return redirect()->route('students.index')->with('error', 'No student profiles found.');
        }

        $student->load([
            'branch',
            'latestAdmission.course',
            'latestAdmission.package',
            'latestAdmission.instructor',
            'progress',
            'lessonSchedules' => fn($q) => $q->with(['instructor', 'vehicle', 'attendance'])->orderBy('scheduled_date', 'asc'),
            'mockTests',
            'certificates',
            'latestAdmission.invoices.payments',
        ]);

        return view('students.portal', compact('student', 'allStudents'));
    }

    public function exportCsv(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        $students = Student::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with(['branch', 'latestAdmission.course', 'progress'])
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_export_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($students) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Student Code', 'First Name', 'Last Name', 'Phone', 'Email', 
                'Branch', 'Course', 'Enrollment Status', 'Progress (%)', 'Learner License Number', 'LL Expiry Date'
            ]);

            foreach ($students as $s) {
                fputcsv($file, [
                    $s->student_code,
                    $s->first_name,
                    $s->last_name,
                    $s->phone,
                    $s->email,
                    $s->branch?->name ?? 'N/A',
                    $s->latestAdmission?->course?->name ?? 'N/A',
                    $s->status,
                    $s->progress?->overall_progress_percentage ?? 0,
                    $s->learner_license_number ?? 'N/A',
                    $s->ll_expiry_date ? $s->ll_expiry_date->format('Y-m-d') : 'N/A',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
