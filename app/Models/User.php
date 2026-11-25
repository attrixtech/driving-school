<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, BelongsToTenant, HasRoles;

    protected string $guard_name = 'web';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'email',
        'phone',
        'password',
        'role_slug',
        'is_active',
        'avatar',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'preferences' => 'array',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_branches');
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function instructor(): HasOne
    {
        return $this->hasOne(Instructor::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isBusinessOwner(): bool
    {
        return $this->hasAnyRole(['business_owner', 'school_admin', 'super_admin']);
    }

    public function isInstructor(): bool
    {
        return $this->hasRole('instructor');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    public function hasPermission(string $permissionName): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->can($permissionName);
    }

    public function primaryRoleName(): string
    {
        return $this->getRoleNames()->first() ?? $this->role_slug ?? 'guest';
    }

    public function canAccessBranch(?int $branchId): bool
    {
        if (!$branchId || $this->isSuperAdmin() || $this->isBusinessOwner()) {
            return true;
        }

        if ($this->branch_id === $branchId) {
            return true;
        }

        return $this->branches()->where('branches.id', $branchId)->exists();
    }
}
