<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Course;
use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicPortalController extends Controller
{
    public function portal()
    {
        $tenant = Tenant::first();
        $company = $tenant?->company;
        $branches = Branch::where('tenant_id', $tenant?->id)->where('is_active', true)->get();
        $courses = Course::where('tenant_id', $tenant?->id)->where('is_active', true)->with('packages')->get();

        return view('public.portal', compact('tenant', 'company', 'branches', 'courses'));
    }

    public function submitEnquiry(Request $request)
    {
        $tenant = Tenant::first();

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'full_name' => 'required|string|max:150',
            'phone' => 'required|string|max:25',
            'email' => 'nullable|email|max:150',
            'course_id' => 'nullable|exists:courses,id',
            'notes' => 'nullable|string',
        ]);

        $leadNumber = 'WEB-' . date('Y') . '-' . strtoupper(Str::random(5));

        Lead::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $validated['branch_id'],
            'lead_number' => $leadNumber,
            'full_name' => $validated['full_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'course_id' => $validated['course_id'] ?? null,
            'source' => 'website',
            'status' => 'new',
            'follow_up_date' => now()->toDateString(),
            'notes' => $validated['notes'] ?? 'Online public website inquiry submission',
        ]);

        return redirect()->back()->with('success', 'Thank you! Your inquiry has been received. Our admission counselor will contact you shortly.');
    }
}
