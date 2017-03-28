<?php

namespace Spatie\Permission\Contracts;

interface Role
{
    /**
     * A role may be given various permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions();

    /**
     * Find a role by its name and guard name.
     *
     * @param string $name
     * @param string $guardName
     *
     * @return \Spatie\Permission\Contracts\Role
     *
     * @throws RoleDoesNotExist
     */
    public static function findByName(string $name, ?string $guardName): Role;

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     */
    public function hasPermissionTo($permission);
}
