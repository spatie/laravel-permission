<?php

namespace Spatie\Permission\Traits;

use Spatie\Permission\Contracts\Tenant as TenantContact;
use Spatie\Permission\Exceptions\TenantDoesNotExist;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait TenantBase
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.role_tenant_user')
        );
    }

    /**
     * A role belongs to some users of the model associated with its guard.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            'App\User',
            config('permission.table_names.role_tenant_user')
        );
    }

    /**
     * Find a tenant by its primary key.
     *
     * @param string|int $id
     *
     * @return \Spatie\Permission\Contracts\Tenant
     *
     * @throws \Spatie\Permission\Exceptions\TenantDoesNotExist
     */
    public static function findById($id): TenantContact
    {
        $tenant = static::where(config('permission.foreign_keys.tenants.id'), $id)->first();

        if (! $tenant) {
            throw TenantDoesNotExist::create($id);
        }

        return $tenant;
    }
}
