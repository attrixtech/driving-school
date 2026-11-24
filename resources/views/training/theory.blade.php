@extends('layouts.app')

@section('title', 'Traffic Signs & Theory Lessons')
@section('page-title', 'Traffic Signs & Theory Learning Hub')

@section('content')
<div class="space-y-4" x-data="{ 
    activeTab: '{{ $activeTab ?? 'signs' }}',
    newClassModal: false,
    attendModal: false,
    selectedClass: null,
    quizAnswers: {},
    quizSubmitted: false,
    quizScore: 0,
    checkQuiz() {
        let score = 0;
        const total = 5;
        const answers = {
            1: 1, // Stop
            2: 2, // Roundabout right
            3: 1, // Double yellow
            4: 2, // Good samaritan
            5: 2  // Flashing amber
        };
        for (const [qId, correct] of Object.entries(answers)) {
            if (this.quizAnswers[qId] == correct) {
                score++;
            }
        }
        this.quizScore = score;
        this.quizSubmitted = true;
    },
    resetQuiz() {
        this.quizAnswers = {};
        this.quizSubmitted = false;
        this.quizScore = 0;
    }
}">

    <!-- TOP HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3 rounded-lg border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                Traffic Signs & Theory Learning Center
            </h1>
            <p class="text-xs text-slate-500">Central Motor Vehicles Rules 31: Mandatory, Cautionary & Informatory Signs, Driving Mechanics, and RTO Test Theory.</p>
        </div>

        <div class="flex items-center gap-2">
            <button @click="newClassModal = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Schedule Classroom Batch</span>
            </button>
            <a href="{{ route('training.mocktests') }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded text-xs font-semibold transition-colors flex items-center gap-1">
                <span>RTO Mock Tests</span> &rarr;
            </a>
        </div>
    </div>

    <!-- TELEMETRY KPI METRICS BAR -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5">
        <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-9 h-9 rounded bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xs shrink-0">
                <span class="text-sm">🛑</span>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Traffic Signs</div>
                <div class="text-base font-bold text-slate-800">{{ $totalSignsCount }} <span class="text-[11px] font-normal text-slate-500">cataloged</span></div>
            </div>
        </div>

        <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-9 h-9 rounded bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs shrink-0">
                <span class="text-sm">📖</span>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Theory Modules</div>
                <div class="text-base font-bold text-blue-700">{{ $totalModulesCount }} <span class="text-[11px] font-normal text-slate-500">lessons</span></div>
            </div>
        </div>

        <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-9 h-9 rounded bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs shrink-0">
                <span class="text-sm">🏛️</span>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Live Batches</div>
                <div class="text-base font-bold text-indigo-700">{{ $upcomingClassesCount }} <span class="text-[11px] font-normal text-slate-500">scheduled</span></div>
            </div>
        </div>

        <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-9 h-9 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs shrink-0">
                <span class="text-sm">⏱️</span>
            </div>
            <div>
                <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">RTO Theory Req.</div>
                <div class="text-base font-bold text-emerald-700">5.0 <span class="text-[11px] font-normal text-slate-500">hours required</span></div>
            </div>
        </div>
    </div>

    <!-- MAIN TABS NAVIGATION -->
    <div class="bg-white border-b border-slate-200 rounded-t-lg px-3 pt-2">
        <nav class="flex space-x-4 text-xs font-semibold" aria-label="Tabs">
            <button @click="activeTab = 'signs'" 
                    :class="activeTab === 'signs' ? 'border-blue-600 text-blue-600 border-b-2 pb-2' : 'text-slate-500 hover:text-slate-700 pb-2'"
                    class="transition-colors flex items-center gap-1.5">
                <span>🛑 Traffic Signs Directory</span>
                <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600">{{ $totalSignsCount }}</span>
            </button>

            <button @click="activeTab = 'modules'" 
                    :class="activeTab === 'modules' ? 'border-blue-600 text-blue-600 border-b-2 pb-2' : 'text-slate-500 hover:text-slate-700 pb-2'"
                    class="transition-colors flex items-center gap-1.5">
                <span>📖 RTO Theory Curriculum</span>
                <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600">7 Modules</span>
            </button>

            <button @click="activeTab = 'quiz'" 
                    :class="activeTab === 'quiz' ? 'border-blue-600 text-blue-600 border-b-2 pb-2' : 'text-slate-500 hover:text-slate-700 pb-2'"
                    class="transition-colors flex items-center gap-1.5">
                <span>✍️ Interactive Practice Quiz</span>
                <span class="text-[10px] px-1.5 py-0.2 rounded bg-blue-100 text-blue-700 font-bold">5 Qs</span>
            </button>

            <button @click="activeTab = 'classes'" 
                    :class="activeTab === 'classes' ? 'border-blue-600 text-blue-600 border-b-2 pb-2' : 'text-slate-500 hover:text-slate-700 pb-2'"
                    class="transition-colors flex items-center gap-1.5">
                <span>🏫 Classroom Batches & Attendance</span>
                <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600">{{ $theoryClasses->count() }}</span>
            </button>
        </nav>
    </div>

    <!-- TAB 1: TRAFFIC SIGNS DIRECTORY -->
    <div x-show="activeTab === 'signs'" class="space-y-3">
        <!-- Sub-filters & Search -->
        <div class="bg-white p-2.5 rounded-lg border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-2.5">
            <div class="flex flex-wrap items-center gap-1.5 text-xs">
                @php
                    $categories = [
                        'all' => 'All Signs (' . $totalSignsCount . ')',
                        'mandatory' => 'Mandatory / Regulatory',
                        'cautionary' => 'Cautionary / Warning',
                        'informatory' => 'Informatory',
                        'markings' => 'Road Markings',
                        'hand_signals' => 'Hand Signals',
                    ];
                @endphp
                @foreach($categories as $catKey => $catLabel)
                    <a href="{{ route('training.theory', ['category' => $catKey, 'search' => $search, 'tab' => 'signs']) }}" 
                       class="px-2.5 py-1 rounded text-[11px] font-semibold transition-colors {{ $category === $catKey ? 'bg-blue-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                        {{ $catLabel }}
                    </a>
                @endforeach
            </div>

            <!-- Search input -->
            <form method="GET" action="{{ route('training.theory') }}" class="flex items-center gap-1.5">
                <input type="hidden" name="category" value="{{ $category }}">
                <input type="hidden" name="tab" value="signs">
                <div class="relative w-48 sm:w-60">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search sign name or rule..." 
                           class="w-full pl-7 pr-2.5 py-1 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <button type="submit" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-900 text-white rounded text-xs font-medium">Search</button>
            </form>
        </div>

        <!-- Signs Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @forelse($trafficSigns as $sign)
                <div class="bg-white rounded-lg border border-slate-200 p-3 shadow-xs hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <!-- Header with Visual Badge -->
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <!-- Visual Sign Representation -->
                            <div class="w-14 h-14 rounded flex items-center justify-center font-bold text-center shrink-0 border 
                                 {{ $sign['category'] === 'mandatory' ? 'bg-rose-50 border-rose-200' : '' }}
                                 {{ $sign['category'] === 'cautionary' ? 'bg-amber-50 border-amber-200' : '' }}
                                 {{ $sign['category'] === 'informatory' ? 'bg-blue-50 border-blue-200' : '' }}
                                 {{ $sign['category'] === 'markings' ? 'bg-slate-900 text-white border-slate-800' : '' }}
                                 {{ $sign['category'] === 'hand_signals' ? 'bg-purple-50 border-purple-200' : '' }}">
                                
                                @if($sign['symbol_type'] === 'stop')
                                    <div class="w-11 h-11 bg-rose-600 text-white rounded-xs flex items-center justify-center text-[10px] font-black tracking-tight" style="clip-path: polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%);">
                                        STOP
                                    </div>
                                @elseif($sign['symbol_type'] === 'give_way')
                                    <div class="w-10 h-9 border-t-[18px] border-t-rose-600 border-x-[16px] border-x-transparent border-b-0 flex items-center justify-center"></div>
                                @elseif($sign['symbol_type'] === 'no_entry')
                                    <div class="w-10 h-10 rounded-full bg-rose-600 flex items-center justify-center">
                                        <div class="w-7 h-1.5 bg-white"></div>
                                    </div>
                                @elseif($sign['symbol_type'] === 'speed_50')
                                    <div class="w-10 h-10 rounded-full border-4 border-rose-600 bg-white text-slate-900 font-extrabold flex items-center justify-center text-xs">
                                        50
                                    </div>
                                @elseif($sign['symbol_type'] === 'no_parking')
                                    <div class="w-10 h-10 rounded-full bg-blue-600 border-3 border-rose-600 relative flex items-center justify-center text-white font-black text-sm">
                                        P
                                        <div class="absolute inset-0 border-t-2 border-rose-600 rotate-45 top-1/2"></div>
                                    </div>
                                @elseif($sign['symbol_type'] === 'no_stopping')
                                    <div class="w-10 h-10 rounded-full bg-blue-600 border-3 border-rose-600 relative flex items-center justify-center text-white font-bold">
                                        <span class="text-rose-600 font-black text-xl leading-none">&times;</span>
                                    </div>
                                @elseif($sign['symbol_type'] === 'compulsory_left')
                                    <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-lg font-bold">
                                        &larr;
                                    </div>
                                @elseif($sign['symbol_type'] === 'hospital')
                                    <div class="w-10 h-10 bg-blue-600 text-white rounded flex items-center justify-center text-xl font-bold">
                                        +
                                    </div>
                                @elseif($sign['symbol_type'] === 'parking_car')
                                    <div class="w-10 h-10 bg-blue-600 text-white rounded flex items-center justify-center text-lg font-bold">
                                        P
                                    </div>
                                @elseif($sign['symbol_type'] === 'pedestrian_crossing' || $sign['symbol_type'] === 'school_ahead')
                                    <div class="w-10 h-9 border-b-[20px] border-b-rose-600 border-x-[16px] border-x-transparent flex items-center justify-center text-amber-300 text-xs">
                                        🚶
                                    </div>
                                @elseif($sign['symbol_type'] === 'broken_white')
                                    <div class="w-10 h-6 bg-slate-800 flex items-center justify-center gap-1">
                                        <span class="w-2.5 h-1 bg-white inline-block"></span>
                                        <span class="w-2.5 h-1 bg-white inline-block"></span>
                                    </div>
                                @elseif($sign['symbol_type'] === 'double_yellow')
                                    <div class="w-10 h-6 bg-slate-800 flex flex-col justify-center items-center gap-0.5">
                                        <div class="w-8 h-0.5 bg-amber-400"></div>
                                        <div class="w-8 h-0.5 bg-amber-400"></div>
                                    </div>
                                @else
                                    <div class="w-10 h-9 border-b-[20px] border-b-rose-600 border-x-[16px] border-x-transparent flex items-center justify-center text-xs">
                                        ⚠️
                                    </div>
                                @endif
                            </div>

                            <!-- Category & Shape Badge -->
                            <div class="text-right">
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase
                                    {{ $sign['category'] === 'mandatory' ? 'bg-rose-100 text-rose-800' : '' }}
                                    {{ $sign['category'] === 'cautionary' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $sign['category'] === 'informatory' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $sign['category'] === 'markings' ? 'bg-slate-100 text-slate-800' : '' }}
                                    {{ $sign['category'] === 'hand_signals' ? 'bg-purple-100 text-purple-800' : '' }}">
                                    {{ ucwords(str_replace('_', ' ', $sign['category'])) }}
                                </span>
                                <div class="text-[10px] text-slate-400 mt-1">{{ $sign['shape'] }}</div>
                            </div>
                        </div>

                        <!-- Name & Description -->
                        <h3 class="text-xs font-bold text-slate-800 mb-1">{{ $sign['name'] }}</h3>
                        <p class="text-[11px] text-slate-600 leading-snug mb-2">{{ $sign['description'] }}</p>
                    </div>

                    <!-- Bottom Penalty & Tip Box -->
                    <div class="pt-2 border-t border-slate-100 text-[10px] space-y-1 bg-slate-50/70 p-2 rounded">
                        <div class="text-rose-700 font-semibold flex items-center gap-1">
                            <span>⚖️ MV Act:</span>
                            <span class="truncate">{{ $sign['penalty'] }}</span>
                        </div>
                        <div class="text-slate-500 italic flex items-center gap-1">
                            <span>💡 Tip:</span>
                            <span class="truncate">{{ $sign['tip'] }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-10 text-center text-slate-400">
                    No traffic signs found matching your filter or search query.
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 2: RTO THEORY CURRICULUM MODULES -->
    <div x-show="activeTab === 'modules'" class="space-y-3">
        <div class="bg-blue-50/70 border border-blue-200 rounded-lg p-3 text-xs flex items-center justify-between">
            <div>
                <h3 class="font-bold text-blue-900">Standard Motor Driving School Curriculum (Rule 31)</h3>
                <p class="text-[11px] text-blue-700">7 Core Theory Lessons designed to satisfy RTO Learner License & Driving Competency Standards.</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold text-blue-900 bg-white px-2 py-1 rounded border border-blue-200">5.0 Total Hours</span>
            </div>
        </div>

        <div class="space-y-2.5">
            @foreach($theoryModules as $mod)
                <div x-data="{ expanded: {{ $mod['number'] === 1 ? 'true' : 'false' }} }" 
                     class="bg-white border border-slate-200 rounded-lg shadow-xs overflow-hidden transition-all">
                    
                    <!-- Accordion Header -->
                    <button @click="expanded = !expanded" 
                            class="w-full p-3 text-left flex items-center justify-between bg-white hover:bg-slate-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs shrink-0">
                                {{ $mod['number'] }}
                            </span>
                            <div>
                                <h3 class="text-xs font-bold text-slate-800">{{ $mod['title'] }}</h3>
                                <p class="text-[11px] text-slate-500 line-clamp-1">{{ $mod['summary'] }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                                {{ $mod['duration_hours'] }} Hour
                            </span>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" 
                                 :class="expanded ? 'rotate-180' : ''" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    <!-- Accordion Body -->
                    <div x-show="expanded" x-collapse class="p-3 border-t border-slate-200 bg-slate-50/50 space-y-3 text-xs">
                        <div>
                            <h4 class="text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">Core Topics & Practical Principles:</h4>
                            <ul class="space-y-1.5">
                                @foreach($mod['topics'] as $topic)
                                    <li class="flex items-start gap-2 text-slate-700">
                                        <span class="text-blue-600 font-bold">•</span>
                                        <span>{{ $topic }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- RTO Exam Tip Box -->
                        <div class="p-2.5 rounded bg-amber-50 border border-amber-200 text-[11px] flex items-start gap-2 text-amber-900">
                            <span class="text-base shrink-0">💡</span>
                            <div>
                                <strong class="font-bold">Official RTO Exam & Track Tip:</strong>
                                <span>{{ $mod['rto_tip'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- TAB 3: INTERACTIVE PRACTICE QUIZ -->
    <div x-show="activeTab === 'quiz'" class="bg-white rounded-lg border border-slate-200 p-4 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
                <h3 class="text-sm font-bold text-slate-800">RTO Learner License Theory Practice Quiz</h3>
                <p class="text-xs text-slate-500">Test your understanding of road signs, right-of-way rules, and motor vehicle laws.</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-bold text-slate-600">Passing Score: 60% (3 / 5)</span>
            </div>
        </div>

        <!-- Quiz Form -->
        <div class="space-y-4">
            @foreach($practiceQuiz as $q)
                <div class="p-3 rounded-lg border border-slate-200 bg-slate-50/40 text-xs space-y-2">
                    <div class="font-bold text-slate-800 flex items-start gap-2">
                        <span class="w-5 h-5 rounded-full bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-[10px] shrink-0">
                            {{ $q['id'] }}
                        </span>
                        <span>{{ $q['question'] }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pl-7">
                        @foreach($q['options'] as $idx => $opt)
                            <label class="flex items-center gap-2 p-2 rounded border border-slate-200 bg-white cursor-pointer hover:bg-slate-50 transition-colors">
                                <input type="radio" 
                                       name="quiz_q_{{ $q['id'] }}" 
                                       value="{{ $idx }}" 
                                       x-model="quizAnswers[{{ $q['id'] }}]"
                                       :disabled="quizSubmitted"
                                       class="text-blue-600 focus:ring-0">
                                <span class="text-slate-700 font-medium text-[11px]">{{ $opt }}</span>
                            </label>
                        @endforeach
                    </div>

                    <!-- Feedback when submitted -->
                    <div x-show="quizSubmitted" class="pl-7 pt-1 text-[11px]">
                        <div x-show="quizAnswers[{{ $q['id'] }}] == {{ $q['correct_index'] }}" class="text-emerald-700 font-semibold flex items-center gap-1">
                            <span>✓ Correct!</span>
                            <span class="text-slate-500 font-normal">{{ $q['explanation'] }}</span>
                        </div>
                        <div x-show="quizAnswers[{{ $q['id'] }}] != {{ $q['correct_index'] }}" class="text-rose-700 font-semibold flex items-center gap-1">
                            <span>✗ Incorrect.</span>
                            <span class="text-slate-500 font-normal">{{ $q['explanation'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Quiz Action & Result Bar -->
        <div class="pt-3 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div x-show="quizSubmitted" class="text-xs">
                <span class="font-bold">Your Score:</span>
                <span class="text-base font-extrabold px-2 py-0.5 rounded"
                      :class="quizScore >= 3 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                      x-text="quizScore + ' / 5 (' + (quizScore * 20) + '%)'"></span>
                <span class="ml-2 font-semibold" :class="quizScore >= 3 ? 'text-emerald-700' : 'text-rose-700'"
                      x-text="quizScore >= 3 ? 'PASSED - Ready for RTO Computer Test!' : 'Needs Revision. Review traffic signs above.'"></span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="resetQuiz()" x-show="quizSubmitted" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold">
                    Retake Quiz
                </button>
                <button type="button" @click="checkQuiz()" x-show="!quizSubmitted" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                    Submit & Check Answers
                </button>
            </div>
        </div>
    </div>

    <!-- TAB 4: CLASSROOM BATCHES & ATTENDANCE -->
    <div x-show="activeTab === 'classes'" class="space-y-3">
        <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-3 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-800">Scheduled Classroom & Online Theory Batches</h3>
                    <p class="text-[11px] text-slate-500">Group classroom lectures on traffic laws, first aid, and defensive driving.</p>
                </div>
                <button @click="newClassModal = true" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                    + New Batch
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase text-[10px] tracking-wider">
                            <th class="py-2.5 px-3">Session & Topic</th>
                            <th class="py-2.5 px-3">Date & Time</th>
                            <th class="py-2.5 px-3">Branch & Room</th>
                            <th class="py-2.5 px-3">Trainer</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-center">Attended</th>
                            <th class="py-2.5 px-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($theoryClasses as $cls)
                            <tr class="hover:bg-slate-50/75 transition-colors">
                                <td class="py-2.5 px-3">
                                    <div class="font-semibold text-slate-800">{{ $cls->title }}</div>
                                    <div class="text-[10px] text-slate-500 truncate max-w-xs">{{ $cls->description }}</div>
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="font-medium text-slate-800">{{ $cls->session_date->format('M d, Y') }}</div>
                                    <div class="text-[10px] text-slate-500">{{ substr($cls->start_time, 0, 5) }} - {{ substr($cls->end_time, 0, 5) }}</div>
                                </td>
                                <td class="py-2.5 px-3">
                                    <div class="font-medium text-slate-700">{{ $cls->branch?->name }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $cls->room_or_link ?? 'Main Classroom' }}</div>
                                </td>
                                <td class="py-2.5 px-3 text-slate-700">
                                    {{ $cls->instructor?->name ?? 'Guest RTO Trainer' }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-bold uppercase
                                        {{ $cls->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $cls->status }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center font-bold text-slate-800">
                                    {{ $cls->attendances->where('status', 'present')->count() }} / {{ $cls->max_seats }}
                                </td>
                                <td class="py-2.5 px-3 text-right">
                                    <button @click="selectedClass = {{ $cls }}; attendModal = true" 
                                            class="px-2 py-1 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 border border-slate-200 rounded text-[11px] font-semibold transition-colors">
                                        Mark Attendance
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    No theory batches scheduled yet. Click <strong>+ Schedule Classroom Batch</strong> to create a session.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL 1: SCHEDULE NEW THEORY BATCH -->
    <div x-show="newClassModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div @click="newClassModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative inline-block w-full max-w-lg p-5 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-800">Schedule Live Theory Classroom Batch</h3>
                    <button @click="newClassModal = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                </div>

                <form method="POST" action="{{ route('training.theory.classes.store') }}" class="mt-4 space-y-3 text-xs">
                    @csrf
                    
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Session Topic / Title <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" required placeholder="e.g. Mandatory Road Signs & Defensive Driving"
                               class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Operating Branch <span class="text-rose-500">*</span></label>
                            <select name="branch_id" required class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 bg-white">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Assigned Instructor / Trainer</label>
                            <select name="instructor_id" class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 bg-white">
                                <option value="">-- Guest Trainer / None --</option>
                                @foreach($instructors as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Session Date <span class="text-rose-500">*</span></label>
                            <input type="date" name="session_date" value="{{ now()->addDays(2)->toDateString() }}" required
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Start Time <span class="text-rose-500">*</span></label>
                            <input type="time" name="start_time" value="10:00" required
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">End Time <span class="text-rose-500">*</span></label>
                            <input type="time" name="end_time" value="11:30" required
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Max Seat Capacity</label>
                            <input type="number" name="max_seats" value="30" min="5" max="100"
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Classroom Room or Google Meet Link</label>
                            <input type="text" name="room_or_link" placeholder="e.g. Room 102 or meet.google.com/..."
                                   class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Syllabus Overview / Notes</label>
                        <textarea name="description" rows="2" placeholder="Brief topics covered in this batch..."
                                  class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                        <button type="button" @click="newClassModal = false" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold shadow-xs">
                            Schedule Batch
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 2: MARK STUDENT THEORY ATTENDANCE -->
    <div x-show="attendModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div @click="attendModal = false" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity"></div>

            <div class="relative inline-block w-full max-w-md p-5 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Record Theory Attendance</h3>
                        <p class="text-[11px] text-slate-500" x-text="selectedClass ? selectedClass.title : ''"></p>
                    </div>
                    <button @click="attendModal = false" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                </div>

                <form method="POST" :action="'{{ url('/training/theory/classes') }}/' + (selectedClass ? selectedClass.id : '') + '/attend'" class="mt-4 space-y-3 text-xs">
                    @csrf
                    
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Select Enrolled Student <span class="text-rose-500">*</span></label>
                        <select name="student_id" required class="w-full px-2.5 py-1.5 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 bg-white">
                            <option value="">-- Choose Student --</option>
                            @foreach($students as $st)
                                <option value="{{ $st->id }}">{{ $st->full_name }} ({{ $st->student_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Attendance Status <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <label class="flex items-center gap-2 p-2 rounded border border-emerald-200 bg-emerald-50/50 cursor-pointer">
                                <input type="radio" name="status" value="present" checked class="text-emerald-600 focus:ring-0">
                                <span class="font-bold text-emerald-800">Present (Attended)</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded border border-rose-200 bg-rose-50/50 cursor-pointer">
                                <input type="radio" name="status" value="absent" class="text-rose-600 focus:ring-0">
                                <span class="font-bold text-rose-800">Absent</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                        <button type="button" @click="attendModal = false" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold shadow-xs">
                            Save Attendance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
