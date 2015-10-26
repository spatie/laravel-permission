<?php namespace Spatie\Permission\Contracts;

interface RoleContract
{
    /**
     * A role may be given various permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function permissions();

    /**
     * Find a role by its name.
     *
     * @param string $name
     *
     * @throws \Spatie\Permissions\Exceptions\RoleDoesNotExist
     */
    static function findByName($name);

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
}
