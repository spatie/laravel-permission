<?php

namespace Spatie\Permission\Traits;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\RoleContract;
use Spatie\Permission\Contracts\PermissionContract;

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
        return $this->belongsToMany(config('laravel-permission.role'), 'user_has_roles');
    }

    /**
     * A user may have multiple direct permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(config('laravel-permission.permission'), 'user_has_permissions');
    }

    /**
     * Assign the given role to the user.
     *
     * @param string|RoleContract $role
     *
     * @return RoleContract
     */
    public function assignRole($role)
    {
        $this->roles()->save($this->getStoredRole($role));
    }

    /**
     * Revoke the given role from the user.
     *
     * @param string|RoleContract $role
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
     * @param string|RoleContract|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasRole($roles)
    {
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if ($roles instanceof RoleContract) {
            return $this->roles->contains('id', $roles->id);
        }

        return (bool) !!$roles->intersect($this->roles)->count();
    }

    /**
     * Determine if the user has any of the given role(s).
     *
     * @param string|RoleContract|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasAnyRole($roles)
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if the user has all of the given role(s).
     *
     * @param string|RoleContract|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasAllRoles($roles)
    {
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if ($roles instanceof RoleContract) {
            return $this->roles->contains('id', $roles->id);
        }

        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof RoleContract ? $role->name : $role;
        });

        return $roles->intersect($this->roles->lists('name')) == $roles;
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|PermissionContract $permission
     *
     * @return bool
     */
    public function hasPermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = app(PermissionContract::class)->findByName($permission);
        }

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    /**
     * @deprecated deprecated since version 1.0.1, use hasPermissionTo instead
     *
     * Determine if the user may perform the given permission.
     *
     * @param PermissionContract $permission
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
     * @param PermissionContract $permission
     *
     * @return bool
     */
    protected function hasPermissionViaRole(PermissionContract $permission)
    {
        return $this->hasRole($permission->roles);
    }

    /**
     * Determine if the user has has the given permission.
     *
     * @param PermissionContract $permission
     *
     * @return bool
     */
    protected function hasDirectPermission(PermissionContract $permission)
    {
        if (is_string($permission)) {
            $permission = app(PermissionContract::class)->findByName($permission);
        }

        return $this->permissions->contains('id', $permission->id);
    }

    /**
     * @param $role
     *
     * @return RoleContract
     */
    protected function getStoredRole($role)
    {
        if (is_string($role)) {
            return app(RoleContract::class)->findByName($role);
        }

        return $role;
    }
}
