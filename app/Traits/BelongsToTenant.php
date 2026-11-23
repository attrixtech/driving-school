<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            if (empty($model->tenant_id)) {
                $tenantId = session('active_tenant_id') ?? Auth::user()?->tenant_id;
                if ($tenantId) {
                    $model->tenant_id = $tenantId;
                }
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = session('active_tenant_id') ?? Auth::user()?->tenant_id;
            // Only apply tenant filter if user is not a platform superadmin without tenant context
            if ($tenantId && !(Auth::check() && Auth::user()->role_slug === 'super_admin' && !session('active_tenant_id'))) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
