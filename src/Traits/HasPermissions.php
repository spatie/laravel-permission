<?php

namespace Spatie\Permission\Traits;

use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\GuardMismatch;

trait HasPermissions
{
    /**
     * Grant the given permission(s) to a role.
     *
     * @param string|array|Permission|\Illuminate\Support\Collection $permissions
     *
     * @return HasPermissions
     */
    public function givePermissionTo(...$permissions)
    {
        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                return $this->getStoredPermission($permission);
            })
            ->each(function ($permission) {
                $this->checkGuardMatching($permission);
            })
            ->all();

        $this->permissions()->saveMany($permissions);

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Remove all current permissions and set the given ones.
     *
     * @param array ...$permissions
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
     * @param $permission
     *
     * @return HasPermissions
     */
    public function revokePermissionTo($permission)
    {
        $this->permissions()->detach($this->getStoredPermission($permission));

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * @param string|array|Permission|\Illuminate\Support\Collection $permissions
     *
     * @return Permission
     */
    protected function getStoredPermission($permissions)
    {
        if (is_string($permissions)) {
            return app(Permission::class)->findByName($permissions, $this->getOwnGuardName());
        }

        if (is_array($permissions)) {
            return app(Permission::class)->whereIn('name', $permissions)->where('guard_name', $this->getOwnGuardName())->get();
        }

        return $permissions;
    }

    protected function checkGuardMatching($roleOrPermission)
    {
        if ($roleOrPermission->guard_name !== $this->getOwnGuardName()) {
            throw new GuardMismatch();
        }
    }

    protected function isGuard(): bool
    {
        return (bool) $this->getOwnGuardName();
    }

    protected function getOwnGuardName()
    {
        return $this->guard_name ?? array_search(get_class($this), $this->getAllAuthGuardProviderModels());
    }

    protected function getAllAuthGuardProviderModels(): array
    {
        return collect(config('auth.guards'))
            ->map(function ($guard) {
                return config("auth.providers.{$guard['provider']}.model");
            })->toArray();
    }
}
