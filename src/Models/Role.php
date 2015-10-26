<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\RefreshesPermissionCache;

class Role extends Model implements RoleContract
{
    use HasPermissions;
    use RefreshesPermissionCache;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    public $guarded = ['id'];

    /**
     * A role may be given various permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(
            config('laravel-permission.models.permission'),
            config('laravel-permission.tables.role_has_permissions')
        );
    }

    /**
     * A role may be assigned to various users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(
            config('laravel-permission.models.user'),
            config('laravel-permission.tables.user_has_roles')
        );
    }

    /**
     * Find a role by its name.
     *
     * @param string $name
     *
     * @throws RoleDoesNotExist
     */
    public static function findByName($name)
    {
        $role = static::where('name', $name)->first();

        if (!$role) {
            throw new RoleDoesNotExist();
        }

        return $role;
    }
}
