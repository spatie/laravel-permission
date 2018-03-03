<?php

namespace Spatie\Permission\Traits;

use Spatie\Permission\Guard;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasPermissions
{
    public static function bootHasPermissions()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->permissions()->detach();
        });
    }

    /**
     * A model may have multiple direct permissions.
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.permission'),
            'model',
            config('permission.table_names.model_has_permissions'),
            'model_id',
            'permission_id'
        );
    }

    /**
     * Scope the model query to certain permissions only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePermission(Builder $query, $permissions): Builder
    {
        $permissions = $this->convertToPermissionModels($permissions);

        $rolesWithPermissions = array_unique(array_reduce($permissions, function ($result, $permission) {
            return array_merge($result, $permission->roles->all());
        }, []));

        return $query->where(function ($query) use ($permissions, $rolesWithPermissions) {
            $query->whereHas('permissions', function ($query) use ($permissions) {
                $query->where(function ($query) use ($permissions) {
                    foreach ($permissions as $permission) {
                        $query->orWhere(config('permission.table_names.permissions').'.id', $permission->id);
                    }
                });
            });
            if (count($rolesWithPermissions) > 0) {
                $query->orWhereHas('roles', function ($query) use ($rolesWithPermissions) {
                    $query->where(function ($query) use ($rolesWithPermissions) {
                        foreach ($rolesWithPermissions as $role) {
                            $query->orWhere(config('permission.table_names.roles').'.id', $role->id);
                        }
                    });
                });
            }
        });
    }

    /**
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return array
     */
    protected function convertToPermissionModels($permissions): array
    {
        if ($permissions instanceof Collection) {
            $permissions = $permissions->all();
        }

        $permissions = array_wrap($permissions);

        return array_map(function ($permission) {
            if ($permission instanceof Permission) {
                return $permission;
            }

            return app(Permission::class)->findByName($permission, $this->getDefaultGuardName());
        }, $permissions);
    }

    /**
     * Determine if the model may perform the given permission.
     *
     * @param string|\Spatie\Permission\Contracts\Permission $permission
     * @param string|null $guardName
     *
     * @return bool
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if (is_string($permission)) {
            $permission = app(Permission::class)->findByName(
                $permission,
                $guardName ?? $this->getDefaultGuardName()
            );
        }

        if (is_int($permission)) {
            $permission = app(Permission::class)->findById($permission, $this->getDefaultGuardName());
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
    public function hasAnyPermission(...$permissions): bool
    {
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

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
     * @param \Spatie\Permission\Contracts\Permission $permission
     *
     * @return bool
     */
    protected function hasPermissionViaRole(Permission $permission): bool
    {
        return $this->hasRole($permission->roles);
    }

    /**
     * Determine if the model has the given permission.
     *
     * @param string|\Spatie\Permission\Contracts\Permission $permission
     *
     * @return bool
     */
    public function hasDirectPermission($permission): bool
    {
        if (is_string($permission)) {
            $permission = app(Permission::class)->findByName($permission, $this->getDefaultGuardName());
            if (! $permission) {
                return false;
            }
        }

        if (is_int($permission)) {
            $permission = app(Permission::class)->findById($permission, $this->getDefaultGuardName());
            if (! $permission) {
                return false;
            }
        }

        return $this->permissions->contains('id', $permission->id);
    }

    /**
     * Return all the permissions the model has via roles.
     */
    public function getPermissionsViaRoles(): Collection
    {
        return $this->load('roles', 'roles.permissions')
            ->roles->flatMap(function ($role) {
                return $role->permissions;
            })->sort()->values();
    }

    /**
     * Return all the permissions the model has, both directly and via roles.
     */
    public function getAllPermissions(): Collection
    {
        return $this->permissions
            ->merge($this->getPermissionsViaRoles())
            ->sort()
            ->values();
    }

    /**
     * Grant the given permission(s) to a role.
     *
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return $this
     */
    public function givePermissionTo(...$permissions)
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                return $this->getStoredPermission($permission);
            })
            ->each(function ($permission) {
                $this->ensureModelSharesGuard($permission);
            })
            ->all();

        $this->permissions()->saveMany($permissions);

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Remove all current permissions and set the given ones.
     *
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return $this
     */
    public function syncPermissions(...$permissions)
    {
        $this->permissions()->detach();

        return $this->givePermissionTo($permissions);
    }

    /**
     * Revoke the given permission.
     *
     * @param \Spatie\Permission\Contracts\Permission|\Spatie\Permission\Contracts\Permission[]|string|string[] $permission
     *
     * @return $this
     */
    public function revokePermissionTo($permission)
    {
        $this->permissions()->detach($this->getStoredPermission($permission));

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return \Spatie\Permission\Contracts\Permission|\Spatie\Permission\Contracts\Permission[]|\Illuminate\Support\Collection
     */
    protected function getStoredPermission($permissions)
    {
        if (is_numeric($permissions)) {
            return app(Permission::class)->findById($permissions, $this->getDefaultGuardName());
        }

        if (is_string($permissions)) {
            return app(Permission::class)->findByName($permissions, $this->getDefaultGuardName());
        }

        if (is_array($permissions)) {
            return app(Permission::class)
                ->whereIn('name', $permissions)
                ->whereIn('guard_name', $this->getGuardNames())
                ->get();
        }

        return $permissions;
    }

    /**
     * @param \Spatie\Permission\Contracts\Permission|\Spatie\Permission\Contracts\Role $roleOrPermission
     *
     * @throws \Spatie\Permission\Exceptions\GuardDoesNotMatch
     */
    protected function ensureModelSharesGuard($roleOrPermission)
    {
        if (! $this->getGuardNames()->contains($roleOrPermission->guard_name)) {
            throw GuardDoesNotMatch::create($roleOrPermission->guard_name, $this->getGuardNames());
        }
    }

    protected function getGuardNames(): Collection
    {
        return Guard::getNames($this);
    }

    protected function getDefaultGuardName(): string
    {
        return Guard::getDefaultName($this);
    }

    /**
     * Forget the cached permissions.
     */
    public function forgetCachedPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
