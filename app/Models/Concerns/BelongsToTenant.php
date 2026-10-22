<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Isolates tenant-owned rows. Request-supplied tenant_id is never trusted
 * when a TenantContext is active: it is overwritten on create and rejected
 * on update if it does not match the context.
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            $tenantId = TenantContext::id();
            if ($tenantId === null) {
                return;
            }

            $builder->where($builder->getModel()->getTable().'.tenant_id', $tenantId);
        });

        static::creating(function (Model $model): void {
            $contextId = TenantContext::id();
            if ($contextId === null) {
                return;
            }

            $model->setAttribute('tenant_id', $contextId);
        });

        static::updating(function (Model $model): void {
            $contextId = TenantContext::id();
            if ($contextId === null) {
                return;
            }

            if ((int) $model->getAttribute('tenant_id') !== $contextId) {
                throw new \InvalidArgumentException('Cannot reassign a record to another tenant.');
            }
        });
    }
}
