<?php

namespace Spatie\Permission\Traits;

use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\GuardMismatch;

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
     * @param \Spatie\Permission\Contracts\Permission|string $permission
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
     * @return \Spatie\Permission\Contracts\Permission
     */
    protected function getStoredPermission($permissions): Permission
    {
        if (is_string($permissions)) {
            return app(Permission::class)->findByName($permissions, $this->getOwnGuardName());
        }

        if (is_array($permissions)) {
            return app(Permission::class)->whereIn('name', $permissions)->where('guard_name', $this->getOwnGuardName())->get();
        }

        return $permissions;
    }

    /**
     * @param \Spatie\Permission\Contracts\Permission|\Spatie\Permission\Contracts\Role $roleOrPermission
     *
     * @throws \Spatie\Permission\Exceptions\GuardMismatch
     */
    protected function checkGuardMatching($roleOrPermission)
    {
        if ($roleOrPermission->guard_name !== $this->getOwnGuardName()) {
            throw new GuardMismatch();
        }
    }

    /**
     * Check if the class is assigned to a guard in `config/auth.php`.
     */
    protected function isAssignedToGuard(): bool
    {
        return (bool) $this->getOwnGuardName();
    }

    /**
     * Get the name of the guard that this class is assigned to based on it's `guard_name` property or the auth config
     * file.
     */
    protected function getOwnGuardName(): string
    {
        return $this->guard_name ?? array_search(get_class($this), $this->getAllAuthGuardProviderModels());
    }

    /**
     * Get an array of all models assigned to guards with the guard names as the keys.
     */
    protected function getAllAuthGuardProviderModels(): array
    {
        return collect(config('auth.guards'))
            ->map(function ($guard) {
                return config("auth.providers.{$guard['provider']}.model");
            })->toArray();
    }
}
