<?php

namespace App\Models\Concerns;

use App\Models\Business;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tenant'a ait modeller icin global scope + otomatik business_id atamasi.
 *
 * Guvenlik: Tenant baglami varken tum sorgular otomatik olarak
 * business_id ile filtrelenir; baska isletmenin verisine erisim
 * ORM seviyesinde engellenir (IDOR korumasi).
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Tenancy::check()) {
                $builder->where($builder->getModel()->getTable().'.business_id', Tenancy::id());
            }
        });

        static::creating(function (Model $model) {
            if (! $model->getAttribute('business_id') && Tenancy::check()) {
                $model->setAttribute('business_id', Tenancy::id());
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** Tenant scope'u olmadan sorgu (yalnizca super admin baglaminda kullanilmali). */
    public static function withoutTenantScope(): Builder
    {
        return static::withoutGlobalScope('tenant');
    }
}
