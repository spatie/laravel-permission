<?php

namespace Spatie\Permission;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait HasRoles
{
    /**
     * A user may have multiple roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
    /**
     * Assign the given role to the user.
     *
     * @param string|Role $role
     *
     * @return mixed
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::whereName($role)->firstOrFail();
        }

        $this->roles()->save($role);
    }
    /**
     * Determine if the user has the given role.
     *
     * @param string|Role|Collection $roles
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

        return !!$roles->intersect($this->roles)->count();
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param Permission $permission
     *
     * @return bool
     */
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::whereName($permission)->firstOrFail();
        }

        return $this->hasRole($permission->roles);
    }
}
