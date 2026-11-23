<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\VehicleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;

        $courses = Course::where('tenant_id', $tenantId)
            ->with(['category', 'packages'])
            ->latest()
            ->get();

        $categories = VehicleCategory::where('tenant_id', $tenantId)->get();

        return view('courses.index', compact('courses', 'categories'));
    }

    public function store(Request $request)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;

        $validated = $request->validate([
            'vehicle_category_id' => 'required|exists:vehicle_categories,id',
            'name' => 'required|string|max:150',
            'transmission_type' => 'required|in:manual,automatic,both',
            'duration_days' => 'required|integer|min:1',
            'total_practical_hours' => 'required|integer|min:1',
            'total_theory_hours' => 'required|integer|min:0',
            'default_fee' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'has_simulator' => 'nullable|boolean',
        ]);

        $code = 'CRS-' . strtoupper(Str::random(6));

        $course = Course::create([
            'tenant_id' => $tenantId,
            'code' => $code,
            'has_simulator' => $request->has('has_simulator'),
            'is_active' => true,
            ...$validated,
        ]);

        ActivityLog::record('courses', 'create', "Created course {$course->name} ({$course->code})", ['course_id' => $course->id]);

        return redirect()->back()->with('success', "Course {$course->name} created successfully.");
    }

    public function storePackage(Request $request, Course $course)
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'duration_days' => 'required|integer|min:1',
            'practical_sessions_count' => 'required|integer|min:1',
            'theory_sessions_count' => 'required|integer|min:0',
            'package_fee' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $code = 'PKG-' . strtoupper(Str::random(6));

        $package = CoursePackage::create([
            'tenant_id' => $tenantId,
            'course_id' => $course->id,
            'code' => $code,
            'includes_ll_assistance' => $request->has('includes_ll_assistance'),
            'includes_dl_test_assistance' => $request->has('includes_dl_test_assistance'),
            'includes_doorstep_pickup' => $request->has('includes_doorstep_pickup'),
            'is_active' => true,
            ...$validated,
        ]);

        ActivityLog::record('courses', 'create_package', "Created package {$package->name} for {$course->name}", ['package_id' => $package->id]);

        return redirect()->back()->with('success', "Package {$package->name} added to {$course->name}.");
    }
}
