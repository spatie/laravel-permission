<?php namespace Spatie\Permission\Contracts;

interface HasPermissionsContract
{
    /**
     * Grant the given permission to a role.
     *
     * @param  $permission
     *
     * @return HasPermissionsContract
     */
    function givePermissionTo($permission);

    /**
     * Revoke the given permission.
     *
     * @param $permission
     *
     * @return HasPermissionsContract
     */
    function revokePermissionTo($permission);

    /**
     * @param $permission
     *
     * @return PermissionContract
     */
    function getStoredPermission($permission);
}