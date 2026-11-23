<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Dashboard' }} | Webotapp Driving School ERP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-100/70" x-data="{ sidebarOpen: false, quickActionOpen: false, userMenuOpen: false, branchMenuOpen: false }">
    <div class="flex h-screen overflow-hidden">
        
        <!-- SIDEBAR -->
        <aside class="fixed inset-y-0 left-0 z-40 flex flex-col flex-shrink-0 w-60 bg-white border-r border-slate-200 transition-all duration-200 lg:static lg:translate-x-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            
            <!-- School Brand / Logo -->
            <div class="flex items-center justify-between h-12 px-3 border-b border-slate-200 bg-white">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <div class="flex items-center justify-center w-7 h-7 rounded bg-blue-600 text-white font-bold text-xs shadow-sm">
                        DS
                    </div>
                    <div class="flex flex-col leading-tight">
                        <span class="text-xs font-bold text-slate-900 tracking-tight">Webotapp ERP</span>
                        <span class="text-[10px] text-blue-600 font-medium truncate max-w-[140px]">
                            {{ session('active_tenant_name', 'Apex Driving Academy') }}
                        </span>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Navigation Links (Compact & Dense) -->
            <div class="flex-1 overflow-y-auto px-2 py-2 space-y-4 text-xs font-medium text-slate-600">
                
                <!-- Main -->
                <div>
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 font-semibold border-r-2 border-blue-600' : 'hover:bg-slate-100 text-slate-700' }}">
                        <svg class="w-4 h-4 text-slate-400 group-hover:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span>Dashboard</span>
                    </a>
                </div>

                <!-- Admissions & CRM -->
                <div>
                    <div class="px-2 pb-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Admissions & CRM</div>
                    <div class="space-y-0.5">
                        <a href="{{ route('crm.leads') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('crm.leads*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <span>Enquiries / Leads</span>
                            </div>
                            <span class="text-[10px] font-bold px-1.5 py-0.2 bg-blue-100 text-blue-700 rounded-full">
                                {{ \App\Models\Lead::count() }}
                            </span>
                        </a>
                        <a href="{{ route('admissions.create') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('admissions.create') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            <span>New Admission</span>
                        </a>
                        <a href="{{ route('students.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('students*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span>All Students</span>
                        </a>
                        <a href="{{ route('student.portal') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('student.portal') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span>Student Portal</span>
                            </div>
                            <span class="text-[9px] px-1 py-0.2 bg-blue-100 text-blue-700 rounded font-bold">Self-Service</span>
                        </a>
                    </div>
                </div>

                <!-- Training & Scheduling -->
                <div>
                    <div class="px-2 pb-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Training & Schedule</div>
                    <div class="space-y-0.5">
                        <a href="{{ route('schedules.calendar') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('schedules.calendar*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>Lesson Calendar</span>
                        </a>
                        <a href="{{ route('schedules.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('schedules.index') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            <span>Practical Lessons</span>
                        </a>
                        <a href="{{ route('training.progress') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('training.progress*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            <span>Student Progress</span>
                        </a>
                        <a href="{{ route('training.mocktests') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('training.mocktests*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Mock Tests & RTO</span>
                        </a>
                    </div>
                </div>

                <!-- Instructors & Fleet -->
                <div>
                    <div class="px-2 pb-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Fleet & Instructors</div>
                    <div class="space-y-0.5">
                        <a href="{{ route('instructors.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('instructors*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span>Instructors</span>
                        </a>
                        <a href="{{ route('vehicles.index') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('vehicles*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                                <span>Vehicle Registry</span>
                            </div>
                            <span class="text-[10px] font-bold px-1.5 py-0.2 bg-emerald-100 text-emerald-700 rounded-full">
                                {{ \App\Models\Vehicle::where('status', 'active')->count() }}
                            </span>
                        </a>
                        <a href="{{ route('fleet.fuel-maintenance') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('fleet.fuel-maintenance*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span>Fuel & Maintenance</span>
                        </a>
                    </div>
                </div>

                <!-- Courses & Packages -->
                <div>
                    <div class="px-2 pb-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Courses & Packages</div>
                    <div class="space-y-0.5">
                        <a href="{{ route('courses.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('courses*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <span>Courses & Packages</span>
                        </a>
                    </div>
                </div>

                <!-- Finance & Billing -->
                <div>
                    <div class="px-2 pb-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Finance & Billing</div>
                    <div class="space-y-0.5">
                        <a href="{{ route('billing.invoices') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('billing.invoices*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path></svg>
                            <span>GST Invoices</span>
                        </a>
                        <a href="{{ route('billing.payments') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('billing.payments*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span>Receipts & Payments</span>
                        </a>
                        <a href="{{ route('billing.expenses') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('billing.expenses*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Expenses</span>
                        </a>
                    </div>
                </div>

                <!-- Certificates & Licensing -->
                <div>
                    <div class="px-2 pb-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Licensing & Certificates</div>
                    <div class="space-y-0.5">
                        <a href="{{ route('licensing.records') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('licensing*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                            <span>LL & DL Records</span>
                        </a>
                        <a href="{{ route('certificates.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('certificates*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                            <span>Certificates</span>
                        </a>
                    </div>
                </div>

                <!-- Reports & Settings -->
                <div>
                    <div class="px-2 pb-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Reports & Admin</div>
                    <div class="space-y-0.5">
                        <a href="{{ route('reports.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('reports*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span>Reports & Analytics</span>
                        </a>
                        <a href="{{ route('notifications.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('notifications*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                            <span>Communications</span>
                        </a>
                        <a href="{{ route('superadmin.tenants') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('superadmin*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                <span class="font-semibold text-purple-700">SaaS SuperAdmin</span>
                            </div>
                            <span class="text-[9px] px-1 py-0.2 bg-purple-100 text-purple-700 rounded font-bold">Platform</span>
                        </a>
                        <a href="{{ route('settings.company') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-md transition-colors {{ request()->routeIs('settings*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-slate-100 text-slate-700' }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                            <span>Company & Branches</span>
                        </a>
                        <a href="{{ route('public.portal') }}" target="_blank" class="flex items-center justify-between px-2.5 py-1.5 rounded-md transition-colors hover:bg-slate-100 text-slate-700">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                <span>Public Website</span>
                            </div>
                            <span class="text-[9px] text-slate-400">Preview</span>
                        </a>
                    </div>
                </div>

            </div>

            <!-- User Quick Switcher Banner at bottom of sidebar -->
            <div class="p-2.5 border-t border-slate-200 bg-slate-50 text-[11px]">
                <div class="flex items-center justify-between text-slate-500 mb-1">
                    <span class="font-medium">Logged in as:</span>
                    <span class="font-semibold text-blue-700 bg-blue-100 px-1.5 py-0.2 rounded text-[10px]">
                        {{ auth()->user()?->role_slug ?? 'Guest' }}
                    </span>
                </div>
                <div class="text-slate-800 font-semibold truncate">{{ auth()->user()?->name ?? 'User' }}</div>
            </div>

        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
            
            <!-- TOP NAVBAR -->
            <header class="flex items-center justify-between h-12 px-4 bg-white border-b border-slate-200 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-500 hover:text-slate-700 p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    
                    <!-- Branch Selector -->
                    @php
                        $branches = \App\Models\Branch::all();
                        $currentBranchId = session('active_branch_id', $branches->first()?->id);
                        $currentBranchName = session('active_branch_name', $branches->first()?->name ?? 'All Branches');
                    @endphp
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-1.5 px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium border border-slate-200">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span class="max-w-[150px] sm:max-w-none truncate">{{ $currentBranchName }}</span>
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute left-0 mt-1 w-56 bg-white border border-slate-200 rounded-md shadow-lg py-1 z-50 text-xs">
                            <div class="px-3 py-1 text-[10px] uppercase font-bold text-slate-400">Select Operating Branch</div>
                            @foreach($branches as $b)
                                <form method="POST" action="{{ route('branch.switch', $b->id) }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 flex items-center justify-between {{ $currentBranchId == $b->id ? 'text-blue-600 font-semibold bg-blue-50/50' : 'text-slate-700' }}">
                                        <span>{{ $b->name }}</span>
                                        @if($b->is_main_branch)
                                            <span class="text-[9px] bg-slate-200 text-slate-600 px-1 rounded">Main</span>
                                        @endif
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Header Actions -->
                <div class="flex items-center gap-2.5">
                    
                    <!-- Quick Actions Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1 rounded text-xs font-semibold shadow-xs">
                            <span>+ Action</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-1 w-48 bg-white border border-slate-200 rounded-md shadow-lg py-1 z-50 text-xs">
                            <a href="{{ route('crm.leads') }}?new=1" class="block px-3 py-1.5 hover:bg-slate-50 text-slate-700">Add New Enquiry</a>
                            <a href="{{ route('admissions.create') }}" class="block px-3 py-1.5 hover:bg-slate-50 text-slate-700">New Student Admission</a>
                            <a href="{{ route('schedules.create') }}" class="block px-3 py-1.5 hover:bg-slate-50 text-slate-700">Schedule Driving Lesson</a>
                            <a href="{{ route('billing.payments') }}?collect=1" class="block px-3 py-1.5 hover:bg-slate-50 text-slate-700">Collect Student Fee</a>
                            <a href="{{ route('billing.expenses') }}?new=1" class="block px-3 py-1.5 hover:bg-slate-50 text-slate-700">Record Expense</a>
                        </div>
                    </div>

                    <!-- Alerts Dropdown -->
                    @php
                        $alertCount = 2; // e.g. PUC expiring, Insurance expiring
                    @endphp
                    <a href="{{ route('dashboard') }}#alerts-section" class="relative p-1.5 text-slate-500 hover:text-slate-700 rounded-md hover:bg-slate-100" title="Operational Alerts">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-amber-500 rounded-full"></span>
                    </a>

                    <!-- Fast Role Switcher (For seamless testing across all ERP personas) -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-1.5 py-1 px-2 text-xs font-medium text-slate-700 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded">
                            <div class="w-4 h-4 rounded-full bg-blue-600 text-white text-[10px] flex items-center justify-center font-bold">
                                {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
                            </div>
                            <span class="hidden md:inline">{{ auth()->user()?->name ?? 'User' }}</span>
                            <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-1 w-56 bg-white border border-slate-200 rounded-md shadow-lg py-1 z-50 text-xs">
                            <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase">Switch Persona Demo</div>
                            
                            <form method="POST" action="{{ route('auth.quick-login') }}">
                                @csrf
                                <input type="hidden" name="email" value="owner@apexdriving.com">
                                <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 text-slate-700 flex items-center justify-between">
                                    <span>Business Owner</span>
                                    <span class="text-[10px] text-slate-400">Full ERP</span>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('auth.quick-login') }}">
                                @csrf
                                <input type="hidden" name="email" value="manager@apexdriving.com">
                                <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 text-slate-700 flex items-center justify-between">
                                    <span>Branch Manager</span>
                                    <span class="text-[10px] text-slate-400">Branch Ops</span>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('auth.quick-login') }}">
                                @csrf
                                <input type="hidden" name="email" value="rajesh.instructor@apexdriving.com">
                                <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 text-slate-700 flex items-center justify-between">
                                    <span>Instructor (Rajesh)</span>
                                    <span class="text-[10px] text-slate-400">Roster & Attendance</span>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('auth.quick-login') }}">
                                @csrf
                                <input type="hidden" name="email" value="student@apexdriving.com">
                                <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 text-slate-700 flex items-center justify-between">
                                    <span>Student (Arjun)</span>
                                    <span class="text-[10px] text-slate-400">Student Portal</span>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('auth.quick-login') }}">
                                @csrf
                                <input type="hidden" name="email" value="superadmin@webotapp.com">
                                <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 text-slate-700 flex items-center justify-between">
                                    <span>Platform Super Admin</span>
                                    <span class="text-[10px] text-slate-400">SaaS Admin</span>
                                </button>
                            </form>

                            <div class="border-t border-slate-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-red-50 text-red-600 font-medium">
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </header>

            <!-- FLASH ALERTS / NOTICES -->
            @if(session('success'))
                <div class="mx-4 mt-3 px-3 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-md flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-xs">&times;</button>
                </div>
            @endif

            @if(session('error') || (isset($errors) && $errors->any()))
                <div class="mx-4 mt-3 px-3 py-2 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-md">
                    <div class="font-bold mb-0.5">Please check the following:</div>
                    <ul class="list-disc list-inside">
                        @if(session('error'))
                            <li>{{ session('error') }}</li>
                        @endif
                        @if(isset($errors))
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            @endif

            <!-- PAGE CONTENT -->
            <main class="flex-1 overflow-y-auto p-4 bg-slate-50/50">
                {{ $slot ?? '' }}
                @yield('content')
            </main>

        </div>

    </div>
</body>
</html>
