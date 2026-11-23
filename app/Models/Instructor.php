<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Instructor extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $guarded = ['id'];

    protected $casts = [
        'vehicle_types' => 'array',
        'license_expiry_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'monthly_salary' => 'decimal:2',
        'rating' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(InstructorAvailability::class);
    }

    public function lessonSchedules(): HasMany
    {
        return $this->hasMany(LessonSchedule::class);
    }

    public function practicalAssessments(): HasMany
    {
        return $this->hasMany(PracticalAssessment::class);
    }
}
