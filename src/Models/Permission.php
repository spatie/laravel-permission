<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\RefreshesPermissionCache;

class Permission extends Model
{
    use RefreshesPermissionCache;

    public $guarded = ['id'];

    /**
     * A permission can be applied to roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions');
    }

    /**
     * Find a permission by it's name.
     *
     * @param $name
     *
     * @throws PermissionDoesNotExist
     */
    public static function findByName($name)
    {
        $permission = static::where('name', $name)->first();

        if (!$permission) {
            throw new PermissionDoesNotExist();
        }

        return $permission;
    }
}
