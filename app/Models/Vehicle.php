<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $guarded = ['id'];

    protected $casts = [
        'year' => 'integer',
        'odometer_reading' => 'integer',
        'insurance_expiry_date' => 'date',
        'fitness_expiry_date' => 'date',
        'pollution_expiry_date' => 'date',
        'permit_expiry_date' => 'date',
        'road_tax_expiry_date' => 'date',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VehicleDocument::class);
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(VehicleFuelLog::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    public function lessonSchedules(): HasMany
    {
        return $this->hasMany(LessonSchedule::class);
    }

    public function isDocumentExpiringSoon(int $days = 30): bool
    {
        $threshold = now()->addDays($days);
        return ($this->insurance_expiry_date && $this->insurance_expiry_date <= $threshold)
            || ($this->fitness_expiry_date && $this->fitness_expiry_date <= $threshold)
            || ($this->pollution_expiry_date && $this->pollution_expiry_date <= $threshold)
            || ($this->permit_expiry_date && $this->permit_expiry_date <= $threshold);
    }
}
