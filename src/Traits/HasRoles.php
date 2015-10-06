<?php

namespace Spatie\Permission\Traits;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait HasRoles
{
    use HasPermissions;
    use RefreshesPermissionCache;

    /**
     * A user may have multiple roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, config('laravel-permissions.tables.user_has_roles'));
    }

    /**
     * A user may have multiple direct permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, config('laravel-permissions.tables.user_has_permissions'));
    }

    /**
     * Assign the given role to the user.
     *
     * @param string|Role $role
     *
     * @return Role
     */
    public function assignRole($role)
    {
        $this->roles()->save($this->getStoredRole($role));
    }

    /**
     * Revoke the given role from the user.
     *
     * @param string|Role $role
     *
     * @return mixed
     */
    public function removeRole($role)
    {
        $this->roles()->detach($this->getStoredRole($role));
    }

    /**
     * Determine if the user has (one of) the given role(s).
     *
     * @param string|Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasRole($roles)
    {
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }

        return (bool) !!$roles->intersect($this->roles)->count();
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param Permission $permission
     *
     * @return bool
     */
    public function hasPermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::findByName($permission);
        }

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    /**
     * @deprecated deprecated since version 1.0.1, use hasPermissionTo instead
     *
     * Determine if the user may perform the given permission.
     *
     * @param Permission $permission
     *
     * @return bool
     */
    public function hasPermission($permission)
    {
        return $this->hasPermissionTo($permission);
    }

    /**
     * Determine if the user has, via roles, has the given permission.
     *
     * @param Permission $permission
     *
     * @return bool
     */
    protected function hasPermissionViaRole(Permission $permission)
    {
        return $this->hasRole($permission->roles);
    }

    /**
     * Determine if the user has has the given permission.
     *
     * @param Permission $permission
     *
     * @return bool
     */
    protected function hasDirectPermission(Permission $permission)
    {
        if (is_string($permission)) {
            $permission = Permission::findByName($permission);
        }

        return $this->permissions->contains('id', $permission->id);
    }

    /**
     * @param $role
     *
     * @return Role
     */
    protected function getStoredRole($role)
    {
        if (is_string($role)) {
            return Role::findByName($role);
        }

        return $role;
    }
}
