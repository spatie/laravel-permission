<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\RefreshesPermissionCache;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Contracts\Permission as PermissionContract;

class Permission extends Model implements PermissionContract
{
    use RefreshesPermissionCache;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    public $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        if (empty($attributes['guard_name'])) {
            $attributes['guard_name'] = config('auth.defaults.guard');
        }

        parent::__construct($attributes);

        $this->setTable(config('laravel-permission.table_names.permissions'));
    }

    /**
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('laravel-permission.models.role'),
            config('laravel-permission.table_names.role_has_permissions')
        );
    }

    /**
     * Find a permission by its name (and optionally guardName).
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @throws \Spatie\Permission\Exceptions\PermissionDoesNotExist
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public static function findByName(string $name, $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');

        $permission = static::getPermissions()->where('name', $name)->where('guard_name', $guardName)->first();

        if (! $permission) {
            throw new PermissionDoesNotExist();
        }

        return $permission;
    }
}
