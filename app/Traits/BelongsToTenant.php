<?php

namespace App\Traits;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            if (empty($model->tenant_id)) {
                $tenantId = session('active_tenant_id');
                if (!$tenantId && Auth::hasUser()) {
                    $tenantId = Auth::user()?->tenant_id;
                }
                if ($tenantId) {
                    $model->tenant_id = $tenantId;
                }
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            // NEVER apply automatic tenant filter to User model to prevent infinite recursion during Auth resolution
            if ($builder->getModel() instanceof User) {
                return;
            }

            $tenantId = session('active_tenant_id');
            if (!$tenantId && Auth::hasUser()) {
                $tenantId = Auth::user()?->tenant_id;
            }

            if ($tenantId) {
                if (Auth::hasUser() && Auth::user()->role_slug === 'super_admin' && !session('active_tenant_id')) {
                    return;
                }
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
