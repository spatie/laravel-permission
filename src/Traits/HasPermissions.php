<?php

namespace Spatie\Permission\Traits;

use Spatie\Permission\Guard;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;

trait HasPermissions
{
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
