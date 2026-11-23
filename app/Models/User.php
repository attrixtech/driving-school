<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, BelongsToTenant;

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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
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
        return $this->role_slug === 'super_admin';
    }

    public function isBusinessOwner(): bool
    {
        return $this->role_slug === 'business_owner' || $this->role_slug === 'school_admin' || $this->isSuperAdmin();
    }

    public function isInstructor(): bool
    {
        return $this->role_slug === 'instructor';
    }

    public function isStudent(): bool
    {
        return $this->role_slug === 'student';
    }

    public function hasRole(string|array $roles): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (is_string($roles)) {
            $roles = [$roles];
        }

        if (in_array($this->role_slug, $roles)) {
            return true;
        }

        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin() || $this->role_slug === 'business_owner') {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('slug', $permissionSlug))
            ->exists();
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
