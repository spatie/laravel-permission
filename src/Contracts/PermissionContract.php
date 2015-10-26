<?php namespace Spatie\Permission\Contracts;

interface PermissionContract
{
    /**
     * A permission can be applied to roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function roles();

    /**
     * Find a permission by its name.
     *
     * @param string $name
     *
     * @throws \Spatie\Permissions\Exceptions\PermissionDoesNotExist
     */
    static function findByName($name);
}