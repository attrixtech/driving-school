<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $guarded = ['id'];

    protected $casts = [
        'date_of_birth' => 'date',
        'll_issue_date' => 'date',
        'll_expiry_date' => 'date',
        'dl_test_date' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }

    public function latestAdmission(): HasOne
    {
        return $this->hasOne(Admission::class)->latestOfMany();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function lessonSchedules(): HasMany
    {
        return $this->hasMany(LessonSchedule::class);
    }

    public function practicalAssessments(): HasMany
    {
        return $this->hasMany(PracticalAssessment::class);
    }

    public function mockTests(): HasMany
    {
        return $this->hasMany(MockTest::class);
    }

    public function progress(): HasOne
    {
        return $this->hasOne(StudentProgress::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . ($this->last_name ?? ''));
    }

    public function isLearnerLicenseExpiringSoon(int $days = 15): bool
    {
        return $this->ll_expiry_date && $this->ll_expiry_date <= now()->addDays($days);
    }
}
