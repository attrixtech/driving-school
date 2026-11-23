<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TheoryAttendance extends Model
{
    use HasFactory, BelongsToTenant;

    protected $guarded = ['id'];

    public function theoryClass(): BelongsTo
    {
        return $this->belongsTo(TheoryClass::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
