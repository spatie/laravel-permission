<?php namespace Spatie\Permission\Traits;

use Spatie\Permission\Exceptions\RoleDoesNotExist;

trait RoleTrait
{
    use HasPermissions;
    use RefreshesPermissionCache;

    /**
     * A role may be given various permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(config('laravel-permission.permission'), 'role_has_permissions');
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