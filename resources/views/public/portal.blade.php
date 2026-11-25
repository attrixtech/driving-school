<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company?->trade_name ?? 'thrumble.driving-school' }} | Official Training School</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800">
    <div class="min-h-full flex flex-col">
        
        <!-- Header -->
        <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
            <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded bg-blue-600 text-white font-bold flex items-center justify-center text-sm">
                        TD
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 leading-tight text-sm">{{ $company?->trade_name ?? 'thrumble.driving-school' }}</div>
                        <div class="text-[10px] text-slate-400">Government Approved Motor Training School &bull; Agra, UP</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-xs font-semibold">
                    <a href="#courses" class="text-slate-600 hover:text-slate-900">Courses & Packages</a>
                    <a href="#enquiry" class="text-slate-600 hover:text-slate-900">Online Admission</a>
                    <a href="{{ route('login') }}" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded shadow-xs">
                        ERP Staff Portal &rarr;
                    </a>
                </div>
            </div>
        </header>

        <!-- Flash Notice -->
        @if(session('success'))
            <div class="bg-emerald-600 text-white text-xs py-2 px-4 text-center font-medium">
                {{ session('success') }}
            </div>
        @endif

        <!-- Hero Section -->
        <section class="bg-gradient-to-b from-blue-50/60 to-white py-12 px-4 border-b border-slate-200">
            <div class="max-w-4xl mx-auto text-center space-y-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                    Trusted by 10,000+ Drivers in Agra & Uttar Pradesh
                </span>
                <h1 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Learn To Drive With Confidence & Safety
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 max-w-2xl mx-auto">
                    Govt-certified dual-control vehicles, experienced RTO instructors, doorstep pickup, and guaranteed RTO driving license test assistance.
                </p>
                <div class="pt-2 flex justify-center gap-3 text-xs">
                    <a href="#enquiry" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-bold shadow-xs">
                        Enroll Online Now
                    </a>
                    <a href="#courses" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded font-bold">
                        Browse Courses
                    </a>
                </div>
            </div>
        </section>

        <!-- Course Packages Section -->
        <section id="courses" class="max-w-6xl mx-auto px-4 py-10 space-y-6 flex-1">
            <div class="text-center space-y-1">
                <h2 class="text-lg font-bold text-slate-900">Featured Driver Training Courses</h2>
                <p class="text-xs text-slate-500">Choose between manual cars, automatic cars, or two-wheeler motorcycle training.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($courses as $c)
                    <div class="bg-white border border-slate-200 rounded-lg p-4 shadow-xs flex flex-col justify-between text-xs space-y-3">
                        <div>
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $c->category?->code ?? 'LMV' }}
                                </span>
                                <span class="text-base font-black text-slate-900">₹{{ number_format($c->default_fee) }}</span>
                            </div>

                            <h3 class="text-sm font-bold text-slate-900 mt-2">{{ $c->name }}</h3>
                            <p class="text-slate-500 text-[11px] mt-1">{{ $c->description }}</p>

                            <div class="space-y-1.5 mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-600">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-emerald-600 font-bold">&#10003;</span>
                                    <span><strong>{{ $c->total_practical_hours }} Hours</strong> 1-on-1 Practical Driving</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-emerald-600 font-bold">&#10003;</span>
                                    <span><strong>{{ $c->total_theory_hours }} Hours</strong> Traffic Signs & Road Safety</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-emerald-600 font-bold">&#10003;</span>
                                    <span>Dual-control safety vehicles & sanitized cars</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-emerald-600 font-bold">&#10003;</span>
                                    <span>RTO Learner License & Mock Test Included</span>
                                </div>
                            </div>
                        </div>

                        <a href="#enquiry" class="w-full text-center py-2 bg-slate-900 hover:bg-slate-800 text-white rounded font-bold text-xs">
                            Inquire for This Course
                        </a>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Online Admission / Enquiry Form -->
        <section id="enquiry" class="bg-white border-t border-slate-200 py-10 px-4">
            <div class="max-w-xl mx-auto space-y-4">
                <div class="text-center space-y-1">
                    <h2 class="text-lg font-bold text-slate-900">Online Admission & Callback Request</h2>
                    <p class="text-xs text-slate-500">Fill in your contact information and our counselor will assist you with batch timings.</p>
                </div>

                <form method="POST" action="{{ route('public.enquiry.submit') }}" class="p-4 bg-slate-50 border border-slate-200 rounded-lg space-y-3 text-xs">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Select Branch *</label>
                        <select name="branch_id" required class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }} - {{ $b->address }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Your Full Name *</label>
                            <input type="text" name="full_name" required placeholder="e.g. Ramesh Chandra" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Phone Number *</label>
                            <input type="text" name="phone" required placeholder="+91 98370 00000" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Email Address</label>
                            <input type="email" name="email" placeholder="name@thrumble.driving-school" class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Course Interested In</label>
                            <select name="course_id" class="w-full form-select border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                                @foreach($courses as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} (₹{{ number_format($c->default_fee) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Preferred Timings / Remarks</label>
                        <textarea name="notes" rows="2" placeholder="e.g. Morning 7-8 AM, automatic car, female instructor..." class="w-full form-textarea border border-slate-300 rounded px-2.5 py-1.5 text-xs"></textarea>
                    </div>

                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-bold text-xs shadow-xs">
                        Submit Application & Request Callback &rarr;
                    </button>
                </form>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-slate-900 text-slate-400 py-6 px-4 text-xs border-t border-slate-800">
            <div class="max-w-6xl mx-auto space-y-2">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                    <div>
                        &copy; {{ date('Y') }} {{ $company?->trade_name ?? 'thrumble.driving-school' }}. All rights reserved.
                    </div>
                    <div class="flex flex-wrap items-center justify-center gap-4 text-slate-400">
                        <span>{{ $company?->address ?? 'Plot 42, Sanjay Place, Agra, Uttar Pradesh 282002' }}</span>
                        <span>GSTIN: {{ $company?->gstin ?? '09AABCT1234F1Z8' }}</span>
                        <span>Support: {{ $company?->phone ?? '+91 562 401 2345' }}</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-1 text-[11px] text-slate-500">
                    <span>Powered by <strong class="text-slate-300">thrumble.driving-school</strong></span>
                    <div class="flex items-center gap-3">
                        <span>Developed by <a href="https://www.attrixtech.com" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:underline font-medium">Attrix Technologies</a></span>
                        <span>&bull;</span>
                        <span>Marketing by <a href="https://www.infusionn.in" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:underline font-medium">Infusionn</a></span>
                    </div>
                </div>
            </div>
        </footer>

    </div>
</body>
</html>
