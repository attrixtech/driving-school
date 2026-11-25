<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate Verification | thrumble.driving-school</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white border border-slate-200 rounded-lg shadow-sm p-6 space-y-4 text-xs">
        
        <div class="text-center space-y-1">
            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-xl font-bold">
                &#10003;
            </div>
            <h1 class="text-base font-bold text-slate-900 pt-1">Certificate Authenticated</h1>
            <p class="text-emerald-700 font-semibold text-xs">Valid & Genuine Driving School Certificate</p>
        </div>

        <div class="p-3 bg-slate-50 border border-slate-200 rounded space-y-2 text-slate-600">
            <div class="flex justify-between">
                <span class="text-slate-400">Student Name:</span>
                <strong class="text-slate-900">{{ $certificate->student?->full_name }}</strong>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Certificate No:</span>
                <span class="font-mono font-bold text-slate-800">{{ $certificate->certificate_number }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Course / Category:</span>
                <strong class="text-blue-700">{{ $certificate->course_name }} ({{ $certificate->vehicle_category_code }})</strong>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Issuing Academy:</span>
                <span class="text-slate-800 font-medium">{{ $certificate->tenant?->company?->trade_name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Branch:</span>
                <span>{{ $certificate->branch?->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Issue Date:</span>
                <span>{{ $certificate->issue_date->format('d M Y') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Practical Completed:</span>
                <span class="font-bold text-slate-800">{{ $certificate->practical_hours_completed }} Hours</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Grade Achieved:</span>
                <span class="font-bold text-emerald-700">{{ $certificate->final_grade }}%</span>
            </div>
        </div>

        <div class="text-center text-[10px] text-slate-400 pt-2 border-t border-slate-100 space-y-1">
            <div>Authenticated via thrumble.driving-school Security Infrastructure.</div>
            <div>
                Developed by <a href="https://www.attrixtech.com" target="_blank" rel="noopener noreferrer" class="text-slate-600 font-semibold hover:underline">Attrix Technologies</a>
                &bull;
                Marketing by <a href="https://www.infusionn.in" target="_blank" rel="noopener noreferrer" class="text-slate-600 font-semibold hover:underline">Infusionn</a>
            </div>
        </div>

    </div>
</body>
</html>
