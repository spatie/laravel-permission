<?php namespace Spatie\Permission\Contracts;

interface HasRolesContract
{
    /**
     * A user may have multiple roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function roles();

    /**
     * A user may have multiple direct permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function permissions();

    /**
     * Assign the given role to the user.
     *
     * @param string|RoleContract $role
     *
     * @return RoleContract
     */
    function assignRole($role);

    /**
     * Revoke the given role from the user.
     *
     * @param string|RoleContract $role
     *
     * @return mixed
     */
    function removeRole($role);

    /**
     * Determine if the user has (one of) the given role(s).
     *
     * @param string|RoleContract|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    function hasRole($roles);

    /**
     * Determine if the user has any of the given role(s).
     *
     * @param string|RoleContract|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    function hasAnyRole($roles);

    /**
     * Determine if the user has all of the given role(s).
     *
     * @param string|RoleContract|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    function hasAllRoles($roles);

    /**
     * Determine if the user may perform the given permission.
     *
     * @param PermissionContract $permission
     *
     * @return bool
     */
    function hasPermissionTo($permission);

    /**
     * @deprecated deprecated since version 1.0.1, use hasPermissionTo instead
     *
     * Determine if the user may perform the given permission.
     *
     * @param PermissionContract $permission
     *
     * @return bool
     */
    function hasPermission($permission);

    /**
     * Determine if the user has, via roles, has the given permission.
     *
     * @param PermissionContract $permission
     *
     * @return bool
     */
    function hasPermissionViaRole(PermissionContract $permission);

    /**
     * Determine if the user has has the given permission.
     *
     * @param PermissionContract $permission
     *
     * @return bool
     */
    function hasDirectPermission(PermissionContract $permission);

    /**
     * @param $role
     *
     * @return RoleContract
     */
    function getStoredRole($role);
}