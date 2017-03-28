<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\RefreshesPermissionCache;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
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

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            config('laravel-permission.models.role'),
            config('laravel-permission.table_names.role_has_permissions')
        );
    }

    /**
     * Find a permission by its name.
     *
     * @param string $name
     * @param string $guardName
     *
     * @throws PermissionDoesNotExist
     *
     * @return PermissionContract
     */
    public static function findByName(string $name, ?string $guardName = null): PermissionContract
    {
        $guardName = $guardName ?? config('auth.defaults.guard');

        $permission = static::getPermissions()->where('name', $name)->where('guard_name', $guardName)->first();

        if (! $permission) {
            throw new PermissionDoesNotExist();
        }

        return $permission;
    }
}
