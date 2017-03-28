<?php

namespace Spatie\Permission\Traits;

use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Contracts\Permission;

trait HasRoles
{
    use HasPermissions;
    use RefreshesPermissionCache;

    /**
     * A model may have multiple roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function roles()
    {
        return $this->morphedByMany(
            config('laravel-permission.models.role'),
            'model',
            config('laravel-permission.table_names.model_has_roles'),
            'role_id',
            'model_id'
        );
    }

    /**
     * A model may have multiple direct permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function permissions()
    {
        return $this->morphedByMany(
            config('laravel-permission.models.permission'),
            'model',
            config('laravel-permission.table_names.model_has_permissions'),
            'permission_id',
            'model_id'
        );
    }

    /**
     * Scope the model query to certain roles only.
     *
     * @param $query
     * @param string|array|Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function scopeRole($query, $roles)
    {
        if ($roles instanceof Collection) {
            $roles = $roles->toArray();
        }

        if (! is_array($roles)) {
            $roles = [$roles];
        }

        $roles = array_map(function ($role) {
            if ($role instanceof Role) {
                return $role;
            }

            $guardName = $this->getOwnGuardName();

            return app(Role::class)->findByName($role, $guardName);
        }, $roles);

        return $query->whereHas('roles', function ($query) use ($roles) {
            $query->where(function ($query) use ($roles) {
                foreach ($roles as $role) {
                    $query->orWhere(config('laravel-permission.table_names.roles').'.id', $role->id);
                }
            });
        });
    }

    /**
     * Assign the given role to the model.
     *
     * @param array|string|\Spatie\Permission\Models\Role ...$roles
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function assignRole(...$roles)
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                return $this->getStoredRole($role);
            })
            ->each(function ($role) {
                $this->checkGuardMatching($role);
            })
            ->all();

        $this->roles()->saveMany($roles);

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Revoke the given role from the model.
     *
     * @param string|Role $role
     */
    public function removeRole($role)
    {
        $this->roles()->detach($this->getStoredRole($role));
    }

    /**
     * Remove all current roles and set the given ones.
     *
     * @param array ...$roles
     *
     * @return $this
     */
    public function syncRoles(...$roles)
    {
        $this->roles()->detach();

        return $this->assignRole($roles);
    }

    /**
     * Determine if the model has (one of) the given role(s).
     *
     * @param string|array|Role|\Illuminate\Support\Collection $roles
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

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }

            return false;
        }

        return (bool) $roles->intersect($this->roles)->count();
    }

    /**
     * Determine if the model has any of the given role(s).
     *
     * @param string|array|Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasAnyRole($roles)
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if the model has all of the given role(s).
     *
     * @param string|Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasAllRoles($roles)
    {
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }

        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        return $roles->intersect($this->roles->pluck('name')) == $roles;
    }

    /**
     * Determine if the model may perform the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     */
    public function hasPermissionTo($permission)
    {
        if (is_string($permission)) {
            $permission = app(Permission::class)->findByName($permission, $this->getOwnGuardName());
        }

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    /**
     * Determine if the model has any of the given permissions.
     *
     * @param array ...$permissions
     *
     * @return bool
     */
    public function hasAnyPermission(...$permissions)
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the model has, via roles, the given permission.
     *
     * @param Permission $permission
     *
     * @return bool
     */
    protected function hasPermissionViaRole(Permission $permission)
    {
        return $this->hasRole($permission->roles);
    }

    /**
     * Determine if the model has the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     */
    public function hasDirectPermission($permission)
    {
        if (is_string($permission)) {
            $permission = app(Permission::class)->findByName($permission, $this->getOwnGuardName());

            if (! $permission) {
                return false;
            }
        }

        return $this->permissions->contains('id', $permission->id);
    }

    /**
     * @param $role
     *
     * @return Role
     */
    protected function getStoredRole($role)
    {
        if (is_string($role)) {
            return app(Role::class)->findByName($role, $this->getOwnGuardName());
        }

        return $role;
    }

    /**
     * Return all permissions the directory coupled to the model.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDirectPermissions()
    {
        return $this->permissions;
    }

    /**
     * Return all the permissions the model has via roles.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPermissionsViaRoles()
    {
        return $this->load('roles', 'roles.permissions')
            ->roles->flatMap(function ($role) {
                return $role->permissions;
            })->sort()->values();
    }

    /**
     * Return all the permissions the model has, both directly and via roles.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllPermissions()
    {
        return $this->permissions->merge($this->getPermissionsViaRoles())->sort()->values();
    }
}
