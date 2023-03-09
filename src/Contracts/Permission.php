<?php

namespace Spatie\Permission\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

interface Permission
{
    /**
     * A permission can be applied to roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany;

    /**
     * Find a permission by its name.
     *
     * @param  string  $name
     * @param string|null $guardName
     * @return Permission
     *
     * @throws PermissionDoesNotExist
     */
    public static function findByName(string $name, ?string $guardName): self;

    /**
     * Find a permission by its id.
     *
     * @param  int  $id
     * @param string|null $guardName
     * @return Permission
     *
     * @throws PermissionDoesNotExist
     */
    public static function findById(int $id, ?string $guardName): self;

    /**
     * Find or Create a permission by its name and guard name.
     *
     * @param  string  $name
     * @param string|null $guardName
     * @return Permission
     */
    public static function findOrCreate(string $name, ?string $guardName): self;
}
