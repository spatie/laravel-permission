<?php namespace Spatie\Permission\Traits;

use Spatie\Permission\Exceptions\PermissionDoesNotExist;

trait PermissionTrait
{
    use RefreshesPermissionCache;

    /**
     * A permission can be applied to roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(config('laravel-permission.role'), 'role_has_permissions');
    }

    /**
     * Find a permission by its name.
     *
     * @param string $name
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