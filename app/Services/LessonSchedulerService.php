<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Admission;
use App\Models\Instructor;
use App\Models\LessonAttendance;
use App\Models\LessonSchedule;
use App\Models\StudentProgress;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LessonSchedulerService
{
    /**
     * Checks if a conflicting lesson exists for instructor, vehicle or student.
     */
    public function detectConflicts(
        int $tenantId,
        string $date,
        string $startTime,
        string $endTime,
        int $instructorId,
        ?int $vehicleId = null,
        ?int $studentId = null,
        ?int $ignoreScheduleId = null
    ): array {
        $conflicts = [];

        // 1. Instructor Conflict
        $instConflict = LessonSchedule::where('tenant_id', $tenantId)
            ->where('instructor_id', $instructorId)
            ->whereDate('scheduled_date', $date)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->when($ignoreScheduleId, fn($q) => $q->where('id', '!=', $ignoreScheduleId))
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($sub) use ($startTime, $endTime) {
                    $sub->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            })
            ->first();

        if ($instConflict) {
            $instructor = Instructor::find($instructorId);
            $conflicts[] = "Instructor {$instructor?->name} is already booked for another session between {$instConflict->start_time} and {$instConflict->end_time}.";
        }

        // 2. Vehicle Conflict
        if ($vehicleId) {
            $veh = Vehicle::find($vehicleId);
            if ($veh && in_array($veh->status, ['in_maintenance', 'breakdown', 'retired'])) {
                $conflicts[] = "Vehicle {$veh->registration_number} is currently marked as '{$veh->status}'.";
            }

            $vehConflict = LessonSchedule::where('tenant_id', $tenantId)
                ->where('vehicle_id', $vehicleId)
                ->whereDate('scheduled_date', $date)
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->when($ignoreScheduleId, fn($q) => $q->where('id', '!=', $ignoreScheduleId))
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where(function ($sub) use ($startTime, $endTime) {
                        $sub->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                    });
                })
                ->first();

            if ($vehConflict) {
                $conflicts[] = "Vehicle {$veh?->registration_number} is already booked between {$vehConflict->start_time} and {$vehConflict->end_time}.";
            }
        }

        // 3. Student Conflict
        if ($studentId) {
            $stuConflict = LessonSchedule::where('tenant_id', $tenantId)
                ->where('student_id', $studentId)
                ->whereDate('scheduled_date', $date)
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->when($ignoreScheduleId, fn($q) => $q->where('id', '!=', $ignoreScheduleId))
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where(function ($sub) use ($startTime, $endTime) {
                        $sub->where('start_time', '<', $endTime)
                            ->where('end_time', '>', $startTime);
                    });
                })
                ->first();

            if ($stuConflict) {
                $conflicts[] = "Student is already scheduled for another lesson between {$stuConflict->start_time} and {$stuConflict->end_time}.";
            }
        }

        return $conflicts;
    }

    /**
     * Schedules a single training lesson.
     */
    public function scheduleLesson(array $data): LessonSchedule
    {
        $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        $branchId = $data['branch_id'] ?? session('active_branch_id') ?? auth()->user()?->branch_id;

        $conflicts = $this->detectConflicts(
            tenantId: $tenantId,
            date: $data['scheduled_date'],
            startTime: $data['start_time'],
            endTime: $data['end_time'],
            instructorId: $data['instructor_id'],
            vehicleId: $data['vehicle_id'] ?? null,
            studentId: $data['student_id'] ?? null
        );

        if (!empty($conflicts)) {
            throw ValidationException::withMessages([
                'schedule_conflict' => $conflicts,
            ]);
        }

        return DB::transaction(function () use ($data, $tenantId, $branchId) {
            $duration = Carbon::parse($data['end_time'])->diffInMinutes(Carbon::parse($data['start_time']));

            $schedule = LessonSchedule::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'student_id' => $data['student_id'],
                'admission_id' => $data['admission_id'] ?? null,
                'instructor_id' => $data['instructor_id'],
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'lesson_type' => $data['lesson_type'] ?? 'practical',
                'topic' => $data['topic'] ?? 'Practical Driving Session',
                'scheduled_date' => $data['scheduled_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'duration_minutes' => $duration ?: 60,
                'status' => 'scheduled',
                'notes' => $data['notes'] ?? null,
            ]);

            ActivityLog::record(
                'scheduling',
                'create',
                "Scheduled {$schedule->lesson_type} lesson for Student #{$schedule->student_id} with Instructor #{$schedule->instructor_id} on {$schedule->scheduled_date}",
                ['schedule_id' => $schedule->id]
            );

            return $schedule->load(['student', 'instructor', 'vehicle']);
        });
    }

    /**
     * Marks a lesson as completed and records attendance and progress.
     */
    public function completeLesson(LessonSchedule $schedule, array $attendanceData): LessonSchedule
    {
        return DB::transaction(function () use ($schedule, $attendanceData) {
            $schedule->update([
                'status' => 'completed',
            ]);

            $kmDriven = 0.00;
            if (!empty($attendanceData['start_odometer']) && !empty($attendanceData['end_odometer'])) {
                $kmDriven = max(0, $attendanceData['end_odometer'] - $attendanceData['start_odometer']);

                if ($schedule->vehicle_id) {
                    $veh = Vehicle::find($schedule->vehicle_id);
                    if ($veh && $attendanceData['end_odometer'] > $veh->odometer_reading) {
                        $veh->update(['odometer_reading' => $attendanceData['end_odometer']]);
                    }
                }
            }

            LessonAttendance::updateOrCreate(
                ['lesson_schedule_id' => $schedule->id],
                [
                    'tenant_id' => $schedule->tenant_id,
                    'student_id' => $schedule->student_id,
                    'instructor_id' => $schedule->instructor_id,
                    'status' => $attendanceData['status'] ?? 'present',
                    'actual_start_time' => $attendanceData['actual_start_time'] ?? $schedule->start_time,
                    'actual_end_time' => $attendanceData['actual_end_time'] ?? $schedule->end_time,
                    'start_odometer' => $attendanceData['start_odometer'] ?? null,
                    'end_odometer' => $attendanceData['end_odometer'] ?? null,
                    'kilometers_driven' => $kmDriven,
                    'student_rating' => $attendanceData['student_rating'] ?? 5,
                    'student_feedback' => $attendanceData['student_feedback'] ?? null,
                    'instructor_notes' => $attendanceData['instructor_notes'] ?? null,
                ]
            );

            // Recalculate Student Progress
            $progress = StudentProgress::where('student_id', $schedule->student_id)->first();
            if ($progress) {
                $progress->recalculate();
            }

            ActivityLog::record(
                'scheduling',
                'complete',
                "Completed lesson #{$schedule->id} for student #{$schedule->student_id}",
                ['schedule_id' => $schedule->id]
            );

            return $schedule;
        });
    }
}
