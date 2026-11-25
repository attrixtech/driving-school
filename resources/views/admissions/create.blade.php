@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-4" 
     x-data="{
        selectedCourseId: '{{ $lead?->course_id ?? ($courses->first()?->id ?? '') }}',
        selectedPackageId: '',
        discount: 0,
        initialPayment: 0,
        courses: {{ $courses->toJson() }},
        packages: {{ $packages->toJson() }},

        get selectedCourse() {
            return this.courses.find(c => c.id == this.selectedCourseId) || null;
        },
        get filteredPackages() {
            return this.packages.filter(p => p.course_id == this.selectedCourseId);
        },
        get selectedPackage() {
            return this.packages.find(p => p.id == this.selectedPackageId) || null;
        },
        get baseFee() {
            if (this.selectedPackage) return parseFloat(this.selectedPackage.package_fee);
            if (this.selectedCourse) return parseFloat(this.selectedCourse.default_fee);
            return 0;
        },
        get subtotal() {
            return Math.max(0, this.baseFee - parseFloat(this.discount || 0));
        },
        get gstAmount() {
            return Math.round((this.subtotal * 0.18) * 100) / 100;
        },
        get totalAmount() {
            return Math.round((this.subtotal + this.gstAmount) * 100) / 100;
        },
        get balanceDue() {
            return Math.max(0, this.totalAmount - parseFloat(this.initialPayment || 0));
        }
     }">

    <!-- Header -->
    <div class="flex items-center justify-between pb-3 border-b border-slate-200">
        <div>
            <h1 class="text-base font-bold text-slate-900 tracking-tight">New Student Admission</h1>
            <p class="text-xs text-slate-500">Register student, configure driving package, calculate GST, and record initial fee collection.</p>
        </div>
        <a href="{{ route('students.index') }}" class="text-xs text-slate-600 hover:text-slate-900">&larr; Back to Students</a>
    </div>

    @if($lead)
        <div class="px-3.5 py-2.5 bg-blue-50 border border-blue-200 rounded text-xs text-blue-800 flex items-center justify-between">
            <div>
                <strong>Converting Lead:</strong> {{ $lead->full_name }} ({{ $lead->lead_number }}) - Phone: {{ $lead->phone }}
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider bg-blue-200 text-blue-900 px-2 py-0.5 rounded">CRM Converted</span>
        </div>
    @endif

    <form method="POST" action="{{ route('admissions.store') }}" class="space-y-4">
        @csrf
        @if($lead)
            <input type="hidden" name="lead_id" value="{{ $lead->id }}">
        @endif

        <!-- 1. Branch & Basic Details -->
        <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4 space-y-3">
            <h2 class="text-xs font-bold text-slate-800 pb-2 border-b border-slate-100 uppercase tracking-wider text-[11px]">
                1. Branch & Personal Information
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Operating Branch *</label>
                    <select name="branch_id" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ ($branchId == $b->id) ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">First Name *</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $lead ? explode(' ', $lead->full_name)[0] : '') }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $lead && count(explode(' ', $lead->full_name)) > 1 ? implode(' ', array_slice(explode(' ', $lead->full_name), 1)) : '') }}" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Phone Number *</label>
                    <input type="text" name="phone" value="{{ old('phone', $lead?->phone) }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Alternate Phone</label>
                    <input type="text" name="alternate_phone" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $lead?->email) }}" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Gender *</label>
                    <select name="gender" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Blood Group</label>
                    <input type="text" name="blood_group" placeholder="e.g. O+, B+, A+" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Address</label>
                    <textarea name="address" rows="2" class="w-full form-textarea border border-slate-300 rounded px-2.5 py-1.5 text-xs" placeholder="Full residential address..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">City</label>
                        <input type="text" name="city" value="{{ old('city', $lead?->city ?? 'Agra') }}" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Postal Code</label>
                        <input type="text" name="postal_code" placeholder="282002" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Guardian / Parent Name</label>
                        <input type="text" name="guardian_name" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Guardian Contact</label>
                        <input type="text" name="guardian_phone" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. License Tracking Details -->
        <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4 space-y-3">
            <h2 class="text-xs font-bold text-slate-800 pb-2 border-b border-slate-100 uppercase tracking-wider text-[11px]">
                2. Learner License Details (If already issued)
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Learner License Number</label>
                    <input type="text" name="learner_license_number" placeholder="UP-80-LL-2026-XXXX" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">LL Issue Date</label>
                    <input type="date" name="ll_issue_date" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">LL Expiry Date</label>
                    <input type="date" name="ll_expiry_date" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
            </div>
        </div>

        <!-- 3. Course, Package & Instructor Assignment -->
        <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4 space-y-3">
            <h2 class="text-xs font-bold text-slate-800 pb-2 border-b border-slate-100 uppercase tracking-wider text-[11px]">
                3. Course Configuration & Instructor
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Select Training Course *</label>
                    <select name="course_id" x-model="selectedCourseId" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->category?->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Select Package (Optional)</label>
                    <select name="course_package_id" x-model="selectedPackageId" class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        <option value="">-- Standard Course Default --</option>
                        <template x-for="pkg in filteredPackages" :key="pkg.id">
                            <option :value="pkg.id" x-text="pkg.name + ' - ₹' + parseFloat(pkg.package_fee).toLocaleString()"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Assigned Instructor</label>
                    <select name="instructor_id" class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        <option value="">-- Assign Later --</option>
                        @foreach($instructors as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->name }} ({{ implode(', ', $inst->vehicle_types ?? []) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Admission Date *</label>
                    <input type="date" name="admission_date" value="{{ now()->toDateString() }}" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Training Start Date</label>
                    <input type="date" name="training_start_date" value="{{ now()->toDateString() }}" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>
            </div>
        </div>

        <!-- 4. Fee Breakdown & Initial Payment (Live Auto-calculation) -->
        <div class="bg-white border border-slate-200 rounded-md shadow-xs p-4 space-y-3">
            <h2 class="text-xs font-bold text-slate-800 pb-2 border-b border-slate-100 uppercase tracking-wider text-[11px]">
                4. Fee Structure & Payment Collection (18% GST Automated)
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Calculation Summary Panel -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded text-xs space-y-2">
                    <div class="flex justify-between text-slate-600">
                        <span>Base Course / Package Fee:</span>
                        <span class="font-semibold text-slate-900" x-text="'₹' + baseFee.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                    </div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Special Discount (₹):</span>
                        <input type="number" step="0.01" name="discount_amount" x-model.number="discount" min="0" placeholder="0" class="w-24 form-input border border-slate-300 rounded px-2 py-1 text-right text-xs">
                    </div>

                    <div class="flex justify-between text-slate-600 pt-1 border-t border-slate-200">
                        <span>Taxable Subtotal:</span>
                        <span class="font-semibold text-slate-900" x-text="'₹' + subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                    </div>

                    <div class="flex justify-between text-slate-600">
                        <span>GST @ 18% (9% CGST + 9% SGST):</span>
                        <span class="font-semibold text-slate-900" x-text="'₹' + gstAmount.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                    </div>

                    <div class="flex justify-between text-sm font-bold text-slate-900 pt-2 border-t border-slate-200 bg-white p-2 rounded">
                        <span>Total Payable Amount:</span>
                        <span class="text-blue-700" x-text="'₹' + totalAmount.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                    </div>

                    <div class="flex justify-between text-xs font-semibold pt-1" :class="balanceDue <= 0 ? 'text-emerald-700' : 'text-rose-600'">
                        <span>Balance Due After Payment:</span>
                        <span x-text="'₹' + balanceDue.toLocaleString('en-IN', {minimumFractionDigits: 2})"></span>
                    </div>
                </div>

                <!-- Initial Payment Input -->
                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Initial Payment Amount (₹)</label>
                        <input type="number" step="0.01" name="initial_payment_amount" x-model.number="initialPayment" min="0" placeholder="0.00" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs font-semibold text-slate-900">
                        <span class="text-[10px] text-slate-400">Leave 0 if payment will be collected later.</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Payment Mode</label>
                        <select name="payment_mode" class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            <option value="cash">Cash Payment</option>
                            <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
                            <option value="card">Credit / Debit Card</option>
                            <option value="bank_transfer">Net Banking / NEFT</option>
                            <option value="razorpay">Online Payment (Razorpay)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Transaction Ref / Cheque No.</label>
                        <input type="text" name="transaction_reference" placeholder="e.g. UPI/12345678/TXN" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Admission Remarks</label>
                        <input type="text" name="remarks" placeholder="Optional notes regarding batch timings or special instructions" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                    </div>
                </div>

            </div>

        </div>

        <!-- Submit Button Panel -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('students.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-bold shadow-sm">
                Confirm Admission & Generate Invoice &rarr;
            </button>
        </div>

    </form>

</div>
@endsection
