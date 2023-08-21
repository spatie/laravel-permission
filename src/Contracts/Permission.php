<?php

namespace Spatie\Permission\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int|string $id
 * @property string $name
 * @property string|null $guard_name
 *
 * @mixin \Spatie\Permission\Models\Permission
 */
interface Permission
{
    /**
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany;

    /**
     * Find a permission by its name.
     *
     * @return \Spatie\Permission\Contracts\Permission
     *
     * @throws \Spatie\Permission\Exceptions\PermissionDoesNotExist
     */
    public static function findByName(string $name, ?string $guardName): self;

    /**
     * Find a permission by its id.
     *
     * @return \Spatie\Permission\Contracts\Permission
     *
     * @throws \Spatie\Permission\Exceptions\PermissionDoesNotExist
     */
    public static function findById(int|string $id, ?string $guardName): self;

    /**
     * Find or Create a permission by its name and guard name.
     *
     * @return \Spatie\Permission\Contracts\Permission
     *
     */
    public static function findOrCreate(string $name, ?string $guardName): self;
}
