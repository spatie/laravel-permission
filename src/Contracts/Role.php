<?php

namespace Spatie\Permission\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int|string $id
 * @property string $name
 * @property string|null $guard_name
 *
 * @mixin \Spatie\Permission\Models\Role
 *
 * @phpstan-require-extends \Spatie\Permission\Models\Role
 */
interface Role
{
    /**
     * A role may be given various permissions.
     */
    public function permissions(): BelongsToMany;

    /**
     * Find a role by its name and guard name.
     *
     *
     * @throws \Spatie\Permission\Exceptions\RoleDoesNotExist
     */
    public static function findByName(string $name, ?string $guardName): self;

    /**
     * Find a role by its id and guard name.
     *
     *
     * @throws \Spatie\Permission\Exceptions\RoleDoesNotExist
     */
    public static function findById(int|string $id, ?string $guardName): self;

    /**
     * Find or create a role by its name and guard name.
     */
    public static function findOrCreate(string $name, ?string $guardName): self;

    /**
     * Determine if the user may perform the given permission.
     *
     * @param  string|int|\Spatie\Permission\Contracts\Permission|\BackedEnum  $permission
     */
    public function hasPermissionTo($permission, ?string $guardName): bool;
}
