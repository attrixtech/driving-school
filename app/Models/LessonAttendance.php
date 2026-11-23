<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonAttendance extends Model
{
    use HasFactory, BelongsToTenant;

    protected $guarded = ['id'];

    protected $casts = [
        'start_odometer' => 'integer',
        'end_odometer' => 'integer',
        'kilometers_driven' => 'decimal:2',
        'student_rating' => 'integer',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(LessonSchedule::class, 'lesson_schedule_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }
}
