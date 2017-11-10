<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Exceptions\TenantAlreadyExist;
use Spatie\Permission\Exceptions\TenantDoesNotExist;
use Spatie\Permission\Traits\RefreshesPermissionCache;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Contracts\Tenant as TenantContract;

class Tenant extends Model implements TenantContract
{
    use HasPermissions;
    use RefreshesPermissionCache;

    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('permission.table_names.tenants'));
        $this->setKeyName(config('permission.foreign_keys.tenants.id'));
        $this->setKeyType(config('permission.foreign_keys.tenants.key_type'));
    }

    public static function create(array $attributes = [])
    {
        $id = config('permission.foreign_keys.tenants.id');

        if (! empty($attributes[$id]) && static::where($id, $attributes[$id])->first()) {
            throw TenantAlreadyExist::create($attributes[$id]);
        }

        return static::query()->create($attributes);
    }

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
     * @return \Spatie\Permission\Contracts\Tenant|\Spatie\Permission\Models\Tenant
     *
     * @throws \Spatie\Permission\Exceptions\TenantDoesNotExist
     */
    public static function findById($id): TenantContract
    {
        $tenant = static::where(config('permission.foreign_keys.tenants.id'), $id)->first();

        if (! $tenant) {
            throw TenantDoesNotExist::create($id);
        }

        return $tenant;
    }
}
