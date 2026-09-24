<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::hasUser()) {
                $user = Auth::user();
                if ($user && $user->role !== 'super_admin' && !empty($user->tenant_id)) {
                    $builder->where($builder->getModel()->getTable() . '.tenant_id', $user->tenant_id);
                }
            }
        });

        static::creating(function ($model) {
            if (Auth::hasUser() && empty($model->tenant_id)) {
                $user = Auth::user();
                if ($user && !empty($user->tenant_id)) {
                    $model->tenant_id = $user->tenant_id;
                }
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
