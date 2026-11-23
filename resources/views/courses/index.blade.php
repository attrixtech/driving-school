@extends('layouts.app')

@section('content')
<div class="space-y-4" x-data="{ newCourseModal: false, newPkgModal: false, activeCourseId: null }">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 gap-2">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">Courses & Training Packages</h1>
            <p class="text-xs text-slate-500">Configure driving curriculums, practical/theory requirements, and package fees.</p>
        </div>
        <button @click="newCourseModal = true" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>+ New Course</span>
        </button>
    </div>

    <!-- Courses Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($courses as $course)
            <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4 flex flex-col justify-between text-xs space-y-3">
                <div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $course->category?->code ?? 'LMV' }}
                            </span>
                            <span class="font-mono text-[10px] text-slate-400">{{ $course->code }}</span>
                        </div>
                        <span class="text-sm font-bold text-slate-900">₹{{ number_format($course->default_fee, 2) }}</span>
                    </div>

                    <h2 class="text-sm font-bold text-slate-900 mt-2">{{ $course->name }}</h2>
                    <p class="text-slate-500 text-[11px] mt-1 line-clamp-2">{{ $course->description }}</p>

                    <div class="grid grid-cols-3 gap-2 mt-3 p-2 bg-slate-50 rounded border border-slate-100 text-center">
                        <div>
                            <div class="text-[10px] text-slate-400 uppercase font-semibold">Practical</div>
                            <div class="font-bold text-slate-800">{{ $course->total_practical_hours }} Hrs</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400 uppercase font-semibold">Theory</div>
                            <div class="font-bold text-slate-800">{{ $course->total_theory_hours }} Hrs</div>
                        </div>
                        <div>
                            <div class="text-[10px] text-slate-400 uppercase font-semibold">Duration</div>
                            <div class="font-bold text-slate-800">{{ $course->duration_days }} Days</div>
                        </div>
                    </div>

                    <!-- Packages for this Course -->
                    <div class="mt-3 pt-2 border-t border-slate-100 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-700">
                            <span>Available Packages ({{ $course->packages->count() }})</span>
                            <button @click="activeCourseId = {{ $course->id }}; newPkgModal = true" class="text-blue-600 hover:text-blue-800 text-[10px] font-semibold">
                                + Add Package
                            </button>
                        </div>

                        @forelse($course->packages as $pkg)
                            <div class="flex items-center justify-between p-2 rounded bg-slate-50 border border-slate-200/60 text-[11px]">
                                <div>
                                    <span class="font-semibold text-slate-800">{{ $pkg->name }}</span>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $pkg->practical_sessions_count }} Practical + {{ $pkg->theory_sessions_count }} Theory
                                    </div>
                                </div>
                                <span class="font-bold text-slate-900">₹{{ number_format($pkg->package_fee, 2) }}</span>
                            </div>
                        @empty
                            <div class="text-[10px] text-slate-400 italic">No packages attached yet. Standard default fee applies.</div>
                        @endforelse
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400">Transmission: <strong class="text-slate-700 capitalize">{{ $course->transmission_type }}</strong></span>
                    <a href="{{ route('admissions.create') }}?course_id={{ $course->id }}" class="text-blue-600 font-semibold hover:underline">
                        Admit Student &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- NEW COURSE MODAL -->
    <div x-show="newCourseModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="newCourseModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Create New Driving Course</h3>
                <button @click="newCourseModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form method="POST" action="{{ route('courses.store') }}" class="p-4 space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Vehicle Category *</label>
                    <select name="vehicle_category_id" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Course Name *</label>
                    <input type="text" name="name" required placeholder="e.g. LMV Four-Wheeler Comprehensive Driving" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Transmission *</label>
                        <select name="transmission_type" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="manual">Manual Transmission</option>
                            <option value="automatic">Automatic Transmission</option>
                            <option value="both">Both (Dual Training)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Default Base Fee (₹) *</label>
                        <input type="number" step="0.01" name="default_fee" required placeholder="7500.00" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Practical Hours *</label>
                        <input type="number" name="total_practical_hours" value="15" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Theory Hours *</label>
                        <input type="number" name="total_theory_hours" value="5" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Duration (Days) *</label>
                        <input type="number" name="duration_days" value="25" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Course Curriculum Description</label>
                    <textarea name="description" rows="2" placeholder="Course highlights..." class="w-full form-textarea border border-slate-300 rounded px-2.5 py-1.5 text-xs"></textarea>
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="newCourseModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold">Save Course</button>
                </div>
            </form>
        </div>
    </div>

    <!-- NEW PACKAGE MODAL -->
    <div x-show="newPkgModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
        <div @click.away="newPkgModal = false" class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-900">Add Package to Course</h3>
                <button @click="newPkgModal = false" class="text-slate-400 hover:text-slate-600 text-sm font-bold">&times;</button>
            </div>

            <form :action="'/courses/' + activeCourseId + '/packages'" method="POST" class="p-4 space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Package Name *</label>
                    <input type="text" name="name" required placeholder="e.g. VIP Doorstep / Fast-Track 10 Days" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Package Fee (₹) *</label>
                        <input type="number" step="0.01" name="package_fee" required placeholder="9500.00" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Duration (Days) *</label>
                        <input type="number" name="duration_days" value="30" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Practical Sessions *</label>
                        <input type="number" name="practical_sessions_count" value="15" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Theory Sessions *</label>
                        <input type="number" name="theory_sessions_count" value="5" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

                <div class="space-y-1.5 pt-1">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="includes_ll_assistance" value="1" checked class="rounded border-slate-300 text-blue-600">
                        <span class="text-slate-700">Includes Learner License (LL) Filing Support</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="includes_dl_test_assistance" value="1" checked class="rounded border-slate-300 text-blue-600">
                        <span class="text-slate-700">Includes RTO Driving License Test Assistance</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="includes_doorstep_pickup" value="1" class="rounded border-slate-300 text-blue-600">
                        <span class="text-slate-700">Includes Doorstep Home Pickup & Drop</span>
                    </label>
                </div>

                <div class="pt-2 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="newPkgModal = false" class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded text-xs">Cancel</button>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-xs font-semibold">Save Package</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
