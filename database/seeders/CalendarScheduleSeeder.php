<?php

namespace Database\Seeders;

use App\Models\Admission;
use App\Models\Branch;
use App\Models\Course;
use App\Models\CoursePackage;
use App\Models\Instructor;
use App\Models\LessonAttendance;
use App\Models\LessonSchedule;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\AdmissionService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CalendarScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (!$tenant) {
            $this->command->error("No tenant found. Run DatabaseSeeder first.");
            return;
        }

        $branchMain = Branch::where('tenant_id', $tenant->id)->where('is_main_branch', true)->first()
            ?? Branch::where('tenant_id', $tenant->id)->first();
        $branchSecondary = Branch::where('tenant_id', $tenant->id)->where('id', '!=', $branchMain->id)->first()
            ?? $branchMain;

        $courseCar = Course::where('tenant_id', $tenant->id)->first();
        $courseBike = Course::where('tenant_id', $tenant->id)->skip(1)->first() ?? $courseCar;

        $packageStandard = CoursePackage::where('tenant_id', $tenant->id)->first();

        // 1. Ensure students exist with admissions
        $admissionService = app(AdmissionService::class);

        $studentsData = [
            [
                'first_name' => 'Priyanka',
                'last_name' => 'Sen',
                'phone' => '+91 98377 11223',
                'email' => 'priyanka.sen@gmail.com',
                'branch_id' => $branchMain->id,
                'course_id' => $courseCar?->id,
                'package_id' => $packageStandard?->id,
            ],
            [
                'first_name' => 'Karthik',
                'last_name' => 'Rao',
                'phone' => '+91 98377 22334',
                'email' => 'karthik.rao@gmail.com',
                'branch_id' => $branchSecondary->id,
                'course_id' => $courseCar?->id,
                'package_id' => $packageStandard?->id,
            ],
            [
                'first_name' => 'Divya',
                'last_name' => 'Deshmukh',
                'phone' => '+91 98377 33445',
                'email' => 'divya.deshmukh@gmail.com',
                'branch_id' => $branchMain->id,
                'course_id' => $courseBike?->id,
                'package_id' => $packageStandard?->id,
            ],
            [
                'first_name' => 'Mohammed',
                'last_name' => 'Zaid',
                'phone' => '+91 98377 44556',
                'email' => 'zaid.mohammed@gmail.com',
                'branch_id' => $branchSecondary->id,
                'course_id' => $courseCar?->id,
                'package_id' => $packageStandard?->id,
            ],
            [
                'first_name' => 'Ananya',
                'last_name' => 'Iyer',
                'phone' => '+91 98377 55667',
                'email' => 'ananya.iyer@gmail.com',
                'branch_id' => $branchMain->id,
                'course_id' => $courseCar?->id,
                'package_id' => $packageStandard?->id,
            ],
        ];

        foreach ($studentsData as $st) {
            $existing = Student::where('tenant_id', $tenant->id)->where('email', $st['email'])->first();
            if (!$existing && $courseCar) {
                try {
                    $admissionService->createAdmission([
                        'branch_id' => $st['branch_id'],
                        'first_name' => $st['first_name'],
                        'last_name' => $st['last_name'],
                        'phone' => $st['phone'],
                        'email' => $st['email'],
                        'date_of_birth' => '2001-05-14',
                        'gender' => 'female',
                        'course_id' => $st['course_id'],
                        'course_package_id' => $st['package_id'],
                        'total_amount' => 7500.00,
                        'initial_payment_amount' => 7500.00,
                        'payment_mode' => 'upi',
                        'remarks' => 'Enrolled via Web Calendar Seeder.',
                    ]);
                } catch (\Throwable $e) {
                    // Ignore if student admission cannot be created
                }
            }
        }

        // Fetch all active admissions, instructors, and vehicles
        $admissions = Admission::where('tenant_id', $tenant->id)->with('student')->get();
        $instructors = Instructor::where('tenant_id', $tenant->id)->where('status', 'active')->get();
        $vehicles = Vehicle::where('tenant_id', $tenant->id)->get();

        if ($admissions->isEmpty() || $instructors->isEmpty()) {
            $this->command->error("Not enough admissions or instructors to seed schedules.");
            return;
        }

        $topics = [
            'Cockpit Drill, Mirror Adjustments & Basic Foot Controls',
            'Clutch Bite Point, Moving Off & Smooth Braking',
            'Gear Shift Coordination (1st to 3rd) & Push-Pull Steering',
            'Left & Right Turns, Blind Spot Checking at T-Junctions',
            'Roundabout Navigation & Dual-Carriageway Lane Discipline',
            'Hill Start Incline Control & Handbrake Balance',
            'Parallel Kerb Parking with Guide Cones',
            'Reverse S-Bend Maneuver & 90-Degree Bay Parking',
            'Overtaking Protocol & Hazard Perception on Busy Arterials',
            'Emergency Braking, ABS Triggering & Collision Avoidance',
            'Night Driving, High-Beam Etiquette & Rain Surface Traction',
            'RTO Track Mock Test: 8-Shape & H-Shape Gradient Mastery',
            'Two-Wheeler Slalom Balance & Emergency Foot Down Technique',
            'City Traffic Rush Hour Navigation & Roundabout Yielding',
        ];

        $timeSlots = [
            ['start' => '07:00:00', 'end' => '08:00:00', 'dur' => 60],
            ['start' => '08:15:00', 'end' => '09:15:00', 'dur' => 60],
            ['start' => '09:30:00', 'end' => '10:30:00', 'dur' => 60],
            ['start' => '10:45:00', 'end' => '11:45:00', 'dur' => 60],
            ['start' => '12:00:00', 'end' => '13:00:00', 'dur' => 60],
            ['start' => '14:30:00', 'end' => '15:30:00', 'dur' => 60],
            ['start' => '16:00:00', 'end' => '17:00:00', 'dur' => 60],
            ['start' => '17:30:00', 'end' => '18:30:00', 'dur' => 60],
        ];

        // We want to fill 21 days: Last week (-7 days), Current week (0 to +6 days), Next week (+7 to +13 days)
        // Current week starts on Monday Nov 23, 2026.
        $startMonday = Carbon::parse('2026-11-23')->startOfWeek(); // 2026-11-23

        $daysToSeed = [];
        // Past week: -7 to -1
        for ($i = -7; $i < 0; $i++) {
            $daysToSeed[] = ['date' => (clone $startMonday)->addDays($i), 'phase' => 'past'];
        }
        // Current week: 0 to 6
        for ($i = 0; $i < 7; $i++) {
            $date = (clone $startMonday)->addDays($i);
            $phase = ($i === 0) ? 'today' : 'current_future';
            $daysToSeed[] = ['date' => $date, 'phase' => $phase];
        }
        // Next week: 7 to 13
        for ($i = 7; $i < 14; $i++) {
            $daysToSeed[] = ['date' => (clone $startMonday)->addDays($i), 'phase' => 'next_week'];
        }

        $sessionCounter = 0;
        $createdCount = 0;

        foreach ($daysToSeed as $dayInfo) {
            $date = $dayInfo['date'];
            $phase = $dayInfo['phase'];
            $dateString = $date->toDateString();

            // Determine number of sessions for this day (6 to 8 sessions)
            $isWeekend = in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
            $numSessions = $isWeekend ? 7 : 6;

            // Pick time slots for today
            $selectedSlots = array_slice($timeSlots, 0, $numSessions);

            foreach ($selectedSlots as $slotIdx => $slot) {
                // Rotate instructors, vehicles, admissions
                $inst = $instructors[($sessionCounter + $slotIdx) % $instructors->count()];
                $veh = $vehicles[($sessionCounter + $slotIdx) % $vehicles->count()];
                $adm = $admissions[($sessionCounter + $slotIdx) % $admissions->count()];
                $topic = $topics[($sessionCounter + $slotIdx) % count($topics)];

                // Check if a session already exists for this instructor/vehicle at this exact date & start_time
                $exists = LessonSchedule::where('tenant_id', $tenant->id)
                    ->where('scheduled_date', $dateString)
                    ->where('start_time', $slot['start'])
                    ->where(function($q) use ($inst, $adm) {
                        $q->where('instructor_id', $inst->id)
                          ->orWhere('student_id', $adm->student_id);
                    })
                    ->first();

                if ($exists) {
                    continue;
                }

                // Determine status
                $status = 'scheduled';
                if ($phase === 'past') {
                    $status = 'completed';
                } elseif ($phase === 'today') {
                    // slots before 14:00 are completed, slots after 14:00 are scheduled
                    if ($slot['start'] < '14:00:00') {
                        $status = 'completed';
                    } else {
                        $status = 'scheduled';
                    }
                } else {
                    $status = 'scheduled';
                }

                $schedule = LessonSchedule::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $adm->branch_id ?? $inst->branch_id ?? $branchMain->id,
                    'student_id' => $adm->student_id,
                    'admission_id' => $adm->id,
                    'instructor_id' => $inst->id,
                    'vehicle_id' => $veh->id,
                    'lesson_type' => 'practical',
                    'topic' => $topic,
                    'scheduled_date' => $dateString,
                    'start_time' => $slot['start'],
                    'end_time' => $slot['end'],
                    'duration_minutes' => $slot['dur'],
                    'status' => $status,
                    'notes' => "Practice session on {$topic}.",
                ]);

                // If completed, add attendance record
                if ($status === 'completed') {
                    $startOdo = 24000 + ($sessionCounter * 18);
                    $endOdo = $startOdo + rand(12, 22);

                    LessonAttendance::create([
                        'tenant_id' => $tenant->id,
                        'lesson_schedule_id' => $schedule->id,
                        'student_id' => $schedule->student_id,
                        'instructor_id' => $schedule->instructor_id,
                        'status' => 'present',
                        'actual_start_time' => $slot['start'],
                        'actual_end_time' => $slot['end'],
                        'start_odometer' => $startOdo,
                        'end_odometer' => $endOdo,
                        'kilometers_driven' => $endOdo - $startOdo,
                        'student_rating' => rand(4, 5),
                        'student_feedback' => 'Good session, clear instructions from instructor.',
                        'instructor_notes' => 'Successfully demonstrated maneuvers with confident mirror checks.',
                    ]);
                }

                $createdCount++;
                $sessionCounter++;
            }
        }

        // 4. Seed Theory Classes & Sample Attendances
        $theoryClassesData = [
            [
                'title' => 'Mandatory Traffic Signs, Road Markings & Speed Regulations',
                'description' => 'Comprehensive visual guide to regulatory signs, penalty structures under MV Act, and road line markings.',
                'branch_id' => $branchMain->id,
                'instructor_id' => $instructors->first()?->id,
                'session_date' => now()->addDays(1)->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '11:30:00',
                'max_seats' => 25,
                'room_or_link' => 'Sanjay Place Classroom 101',
                'status' => 'scheduled',
            ],
            [
                'title' => 'Right of Way, Roundabout Navigation & Highway Discipline',
                'description' => 'Clockwise roundabout entry/exit, pedestrian zebra crossings, and emergency vehicle yield protocols.',
                'branch_id' => $branchMain->id,
                'instructor_id' => $instructors->skip(1)->first()?->id ?? $instructors->first()?->id,
                'session_date' => now()->addDays(3)->toDateString(),
                'start_time' => '14:00:00',
                'end_time' => '15:30:00',
                'max_seats' => 30,
                'room_or_link' => 'Main Audiovisual Training Hall',
                'status' => 'scheduled',
            ],
            [
                'title' => 'Vehicle Anatomy, Pre-Drive Cockpit Checks & Emergency First Aid',
                'description' => 'DSSSM routine, dashboard warning symbols, brake fluid & tyre inspection, and Section 134A Good Samaritan Law.',
                'branch_id' => $branchSecondary->id,
                'instructor_id' => $instructors->skip(2)->first()?->id ?? $instructors->first()?->id,
                'session_date' => now()->addDays(5)->toDateString(),
                'start_time' => '11:00:00',
                'end_time' => '12:30:00',
                'max_seats' => 20,
                'room_or_link' => 'Kamla Nagar Interactive Lab',
                'status' => 'scheduled',
            ],
            [
                'title' => 'RTO Computer Test Prep & Road Safety Theory Masterclass',
                'description' => 'High-yield RTO question bank walkthrough, common exam traps, and mock test scoring review.',
                'branch_id' => $branchMain->id,
                'instructor_id' => $instructors->first()?->id,
                'session_date' => now()->subDays(2)->toDateString(),
                'start_time' => '16:00:00',
                'end_time' => '17:30:00',
                'max_seats' => 30,
                'room_or_link' => 'meet.google.com/rto-thrumble-driving-school',
                'status' => 'completed',
            ],
        ];

        foreach ($theoryClassesData as $tc) {
            $class = \App\Models\TheoryClass::create(array_merge($tc, [
                'tenant_id' => $tenant->id,
            ]));

            if ($class->status === 'completed' && $admissions->isNotEmpty()) {
                foreach ($admissions->take(3) as $adm) {
                    \App\Models\TheoryAttendance::create([
                        'tenant_id' => $tenant->id,
                        'theory_class_id' => $class->id,
                        'student_id' => $adm->student_id,
                        'status' => 'present',
                    ]);
                }
            }
        }

        $this->command->info("Calendar successfully populated with {$createdCount} lesson schedules across 3 weeks!");
    }
}
