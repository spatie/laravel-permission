<?php

namespace Spatie\Permission\Traits;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Restrictable;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\PermissionRegistrar;

trait HasPermissions
{
    /**
     * Scope a query to retrieve only the permission selection related to the given restrictable instance.
     * If the restrictable instance is null, not scoped permissions will be retrieved.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Spatie\Permission\Contracts\Restrictable $restrictable
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRestrictTo(Builder $query, Restrictable $restrictable)
    {
        return $query->wherePivot('restrictable_id', is_null($restrictable) ? null : $restrictable->getRestrictableId())
            ->wherePivot('restrictable_type', is_null($restrictable) ? null : $restrictable->getRestrictableTable());
    }

    /**
     * Grant the given permission(s) to a role.
     * If a restrictable instance is given, given permission(s) is/are scoped to it,
     *  otherwise there won't be a scope for the permission(s).
     *
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     * @param \Spatie\Permission\Contracts\Restrictable $restrictable
     * @return $this
     */
    public function givePermissionTo($permissions, Restrictable $restrictable = null)
    {
        // Permission objects, if directly collected, becomes arrays of fields and the flatten() messes with
        // the map function giving every single Permission field as parameter for getStoredPermission.
        // To avoid this, if a Permission is given an empty collection is created and the permission is pushed inside.
        // In this way, in case of a Permission instance, the object is not flattened,
        // but for arrays, collections and string everything works as expected.
        $permissions = (($permissions instanceof Permission) ? collect()->push($permissions) : collect($permissions))
            ->flatten()
            ->map(function ($permission) {
                return $this->getStoredPermission($permission);
            })
            ->each(function ($permission) {
                $this->ensureGuardIsEqual($permission);
            })
            ->all();

        // If there is no restrictable instance, we won't add anything on the pivot table,
        //  which will default to null values on the restrictable morph.
        // Otherwise we set the references to it
        $restrictable = is_null($restrictable) ? [] : [
            'restrictable_id' => $restrictable->getRestrictableId(),
            'restrictable_type' => $restrictable->getRestrictableTable(),
        ];

        $this->permissions()->saveMany($permissions, $restrictable);

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Remove all current not scoped permissions and set the given ones.
     * If a Restrictable instance is given, permissions will be removed and set only for that instance scope.
     *
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     * @param \Spatie\Permission\Contracts\Restrictable $restrictable
     * @return $this
     */
    public function syncPermissions($permissions, Restrictable $restrictable = null)
    {
        $this->permissions()->restrictTo($restrictable)->detach();

        return $this->givePermissionTo($permissions, $restrictable);
    }

    /**
     * Revoke the given permission.
     * If a Restrictable instance is given, the permission will be removed only for that resource scope.
     *
     * @param \Spatie\Permission\Contracts\Permission|string $permission
     * @param \Spatie\Permission\Contracts\Restrictable $restrictable
     * @return $this
     */
    public function revokePermissionTo($permission, Restrictable $restrictable = null)
    {
        $this->permissions()->restrictTo($restrictable)->detach($this->getStoredPermission($permission));

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     * @return \Spatie\Permission\Contracts\Permission
     */
    protected function getStoredPermission($permissions): Permission
    {
        if (is_string($permissions)) {
            return app(Permission::class)->findByName($permissions, $this->getGuardName());
        }

        if (is_array($permissions)) {
            return app(Permission::class)
                ->whereIn('name', $permissions)
                ->where('guard_name', $this->getGuardName())
                ->get();
        }

        return $permissions;
    }

    /**
     * @param \Spatie\Permission\Contracts\Permission|\Spatie\Permission\Contracts\Role $roleOrPermission
     *
     * @throws \Spatie\Permission\Exceptions\GuardDoesNotMatch
     */
    protected function ensureGuardIsEqual($roleOrPermission)
    {
        if ($roleOrPermission->guard_name !== $this->getGuardName()) {
            throw GuardDoesNotMatch::create($roleOrPermission->guard_name, $this->getGuardName());
        }
    }

    /**
     * Check if the class is assigned to a guard in `config/auth.php`.
     */
    protected function isAssignedToGuard(): bool
    {
        return (bool)$this->getGuardName();
    }

    /**
     * Get the name of the guard that this class is assigned to based on it's `guard_name` property or the auth config
     * file.
     */
    protected function getGuardName(): string
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

    /**
     * Forget the cached permissions.
     */
    public function forgetCachedPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
