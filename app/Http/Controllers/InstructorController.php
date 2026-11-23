<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Instructor;
use App\Models\InstructorAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InstructorController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = session('active_branch_id') ?? auth()->user()?->branch_id;

        $instructors = Instructor::where('tenant_id', $tenantId)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with(['branch', 'availabilities'])
            ->withCount(['lessonSchedules' => fn($q) => $q->where('status', 'completed')])
            ->latest()
            ->paginate(15);

        $branches = Branch::where('tenant_id', $tenantId)->where('is_active', true)->get();

        return view('instructors.index', compact('instructors', 'branches'));
    }

    public function store(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:25',
            'email' => 'nullable|email|max:150',
            'license_number' => 'required|string|max:50',
            'license_expiry_date' => 'nullable|date',
            'experience_years' => 'required|integer|min:0',
            'vehicle_types' => 'required|array',
            'monthly_salary' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
        ]);

        $code = 'INST-' . strtoupper(Str::random(5));

        $instructor = Instructor::create([
            'tenant_id' => $tenantId,
            'instructor_code' => $code,
            'status' => 'active',
            'rating' => 5.00,
            ...$validated,
        ]);

        // Default availability: Mon-Sat 7 AM to 6 PM
        for ($day = 1; $day <= 6; $day++) {
            InstructorAvailability::create([
                'tenant_id' => $tenantId,
                'instructor_id' => $instructor->id,
                'day_of_week' => $day,
                'start_time' => '07:00:00',
                'end_time' => '18:00:00',
                'is_available' => true,
            ]);
        }

        ActivityLog::record('instructors', 'create', "Added instructor {$instructor->name} ({$instructor->instructor_code})", ['instructor_id' => $instructor->id]);

        return redirect()->back()->with('success', "Instructor {$instructor->name} added successfully.");
    }
}
