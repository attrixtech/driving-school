<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In | thrumble.driving-school</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 antialiased text-slate-800">
    <div class="max-w-md w-full space-y-4">
        
        <!-- Brand & Title -->
        <div class="text-center space-y-1">
            <div class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-blue-600 text-white font-bold text-base shadow-sm">
                TD
            </div>
            <h1 class="text-lg font-bold text-slate-900 tracking-tight">thrumble.driving-school</h1>
            <p class="text-xs text-slate-500">Driving School Management & Multi-Branch Operations Platform &bull; Agra, UP</p>
        </div>

        @if(session('success'))
            <div class="px-3 py-2 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="px-3 py-2 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded">
                {{ session('error') ?? $errors->first() }}
            </div>
        @endif

        <!-- Login Card -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-5 space-y-4">
            <form method="POST" action="{{ route('login') }}" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', 'owner@thrumble.driving-school') }}" required autofocus class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-[11px] font-semibold text-slate-700">Password *</label>
                        <span class="text-[10px] text-slate-400">Demo: password123</span>
                    </div>
                    <input type="password" name="password" value="password123" required class="w-full form-input border border-slate-300 rounded px-2.5 py-1.5 text-xs">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-1.5 text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-bold text-xs shadow-xs transition-colors">
                    Sign In to Dashboard &rarr;
                </button>
            </form>

            <!-- 1-Click Fast Persona Switcher for Evaluation -->
            <div class="pt-3 border-t border-slate-100 space-y-2">
                <div class="text-[10px] uppercase font-bold text-slate-400 text-center tracking-wider">
                    Instant 1-Click Demo Login
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs">
                    <form method="POST" action="{{ route('auth.quick-login') }}">
                        @csrf
                        <input type="hidden" name="email" value="owner@thrumble.driving-school">
                        <button type="submit" class="w-full text-left p-2 rounded border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 transition-colors">
                            <div class="font-bold text-blue-900 text-[11px]">Business Owner</div>
                            <div class="text-[9px] text-blue-700">Full ERP Access</div>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('auth.quick-login') }}">
                        @csrf
                        <input type="hidden" name="email" value="manager@thrumble.driving-school">
                        <button type="submit" class="w-full text-left p-2 rounded border border-purple-200 bg-purple-50/50 hover:bg-purple-100/70 transition-colors">
                            <div class="font-bold text-purple-900 text-[11px]">Branch Manager</div>
                            <div class="text-[9px] text-purple-700">Kamla Nagar Branch</div>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('auth.quick-login') }}">
                        @csrf
                        <input type="hidden" name="email" value="rajesh.instructor@thrumble.driving-school">
                        <button type="submit" class="w-full text-left p-2 rounded border border-amber-200 bg-amber-50/50 hover:bg-amber-100/70 transition-colors">
                            <div class="font-bold text-amber-900 text-[11px]">Driving Instructor</div>
                            <div class="text-[9px] text-amber-700">Attendance & Ratings</div>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('auth.quick-login') }}">
                        @csrf
                        <input type="hidden" name="email" value="student@thrumble.driving-school">
                        <button type="submit" class="w-full text-left p-2 rounded border border-emerald-200 bg-emerald-50/50 hover:bg-emerald-100/70 transition-colors">
                            <div class="font-bold text-emerald-900 text-[11px]">Student (Arjun)</div>
                            <div class="text-[9px] text-emerald-700">Training Progress</div>
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <div class="text-center text-[11px] text-slate-500">
            Looking for public course registration? <a href="{{ route('public.portal') }}" class="text-blue-600 font-semibold hover:underline">Visit Public Website</a>
        </div>

        <div class="text-center text-[10px] text-slate-400 pt-1">
            Developed by <a href="https://www.attrixtech.com" target="_blank" rel="noopener noreferrer" class="text-slate-600 font-semibold hover:underline">Attrix Technologies</a>
            &bull;
            Marketing by <a href="https://www.infusionn.in" target="_blank" rel="noopener noreferrer" class="text-slate-600 font-semibold hover:underline">Infusionn</a>
        </div>

    </div>
</body>
</html>
