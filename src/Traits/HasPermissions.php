<?php

namespace Spatie\Permission\Traits;

use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Contracts\Wildcard;
use Spatie\Permission\Events\PermissionAttachedEvent;
use Spatie\Permission\Events\PermissionDetachedEvent;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\WildcardPermissionInvalidArgument;
use Spatie\Permission\Exceptions\WildcardPermissionNotImplementsContract;
use Spatie\Permission\Guard;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\WildcardPermission;

use function Illuminate\Support\enum_value;

trait HasPermissions
{
    private ?string $permissionClass = null;

    private ?string $wildcardClass = null;

    private array $wildcardPermissionsIndex;

    public static function bootHasPermissions(): void
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $teams = app(PermissionRegistrar::class)->teams;
            app(PermissionRegistrar::class)->teams = false;
            if (! $model instanceof Permission) {
                $model->permissions()->detach();
            }
            if ($model instanceof Role) {
                $model->users()->detach();
            }
            app(PermissionRegistrar::class)->teams = $teams;
        });
    }

    public function getPermissionClass(): string
    {
        if (! $this->permissionClass) {
            $this->permissionClass = app(PermissionRegistrar::class)->getPermissionClass();
        }

        return $this->permissionClass;
    }

    public function getWildcardClass(): string
    {
        if (! is_null($this->wildcardClass)) {
            return $this->wildcardClass;
        }

        $this->wildcardClass = '';

        if (config('permission.enable_wildcard_permission')) {
            $this->wildcardClass = config('permission.wildcard_permission', WildcardPermission::class);

            if (! is_subclass_of($this->wildcardClass, Wildcard::class)) {
                throw WildcardPermissionNotImplementsContract::create();
            }
        }

        return $this->wildcardClass;
    }

    /**
     * A model may have multiple direct permissions.
     */
    public function permissions(): BelongsToMany
    {
        $relation = $this->morphToMany(
            config('permission.models.permission'),
            'model',
            config('permission.table_names.model_has_permissions'),
            config('permission.column_names.model_morph_key'),
            app(PermissionRegistrar::class)->pivotPermission
        );

        if (! app(PermissionRegistrar::class)->teams) {
            return $relation;
        }

        $teamsKey = app(PermissionRegistrar::class)->teamsKey;
        $relation->withPivot($teamsKey);

        return $relation->wherePivot($teamsKey, getPermissionsTeamId());
    }

    /**
     * Scope the model query to certain permissions only.
     *
     * @param  string|int|array|Permission|Collection|BackedEnum  $permissions
     */
    public function scopePermission(Builder $query, $permissions, bool $without = false): Builder
    {
        $permissions = $this->convertToPermissionModels($permissions);

        $permissionKey = (new ($this->getPermissionClass())())->getKeyName();
        $roleKey = (new ($this instanceof Role ? static::class : $this->getRoleClass())())->getKeyName();

        $rolesWithPermissions = $this instanceof Role ? [] : array_unique(
            array_reduce($permissions, fn ($result, $permission) => array_merge($result, $permission->roles->all()), [])
        );

        return $query->where(fn (Builder $query) => $query
            ->{! $without ? 'whereHas' : 'whereDoesntHave'}('permissions', fn (Builder $subQuery) => $subQuery
            ->whereIn(config('permission.table_names.permissions').".$permissionKey", array_column($permissions, $permissionKey))
            )
            ->when(count($rolesWithPermissions), fn ($whenQuery) => $whenQuery
                ->{! $without ? 'orWhereHas' : 'whereDoesntHave'}('roles', fn (Builder $subQuery) => $subQuery
                ->whereIn(config('permission.table_names.roles').".$roleKey", array_column($rolesWithPermissions, $roleKey))
                )
            )
        );
    }

    /**
     * Scope the model query to only those without certain permissions,
     * whether indirectly by role or by direct permission.
     *
     * @param  string|int|array|Permission|Collection|BackedEnum  $permissions
     */
    public function scopeWithoutPermission(Builder $query, $permissions): Builder
    {
        return $this->scopePermission($query, $permissions, true);
    }

    /**
     * @param  string|int|array|Permission|Collection|BackedEnum  $permissions
     *
     * @throws PermissionDoesNotExist
     */
    protected function convertToPermissionModels($permissions): array
    {
        if ($permissions instanceof Collection) {
            $permissions = $permissions->all();
        }

        return array_map(function ($permission) {
            if ($permission instanceof Permission) {
                return $permission;
            }

            $permission = enum_value($permission);

            $method = is_int($permission) || PermissionRegistrar::isUid($permission) ? 'findById' : 'findByName';

            return $this->getPermissionClass()::{$method}($permission, $this->getDefaultGuardName());
        }, Arr::wrap($permissions));
    }

    /**
     * Find a permission.
     *
     * @param  string|int|Permission|BackedEnum  $permission
     *
     * @throws PermissionDoesNotExist
     */
    public function filterPermission($permission, ?string $guardName = null): Permission
    {
        $permission = enum_value($permission);

        if (is_int($permission) || PermissionRegistrar::isUid($permission)) {
            $permission = $this->getPermissionClass()::findById(
                $permission,
                $guardName ?? $this->getDefaultGuardName()
            );
        }

        if (is_string($permission)) {
            $permission = $this->getPermissionClass()::findByName(
                $permission,
                $guardName ?? $this->getDefaultGuardName()
            );
        }

        if (! $permission instanceof Permission) {
            throw new PermissionDoesNotExist;
        }

        return $permission;
    }

    /**
     * Determine if the model may perform the given permission.
     *
     * @param  string|int|Permission|BackedEnum  $permission
     *
     * @throws PermissionDoesNotExist
     */
    public function hasPermissionTo($permission, ?string $guardName = null): bool
    {
        if ($this->getWildcardClass()) {
            return $this->hasWildcardPermission($permission, $guardName);
        }

        $permission = $this->filterPermission($permission, $guardName);

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    /**
     * Validates a wildcard permission against all permissions of a user.
     *
     * @param  string|int|Permission|BackedEnum  $permission
     */
    protected function hasWildcardPermission($permission, ?string $guardName = null): bool
    {
        $guardName = $guardName ?? $this->getDefaultGuardName();

        $permission = enum_value($permission);

        if (is_int($permission) || PermissionRegistrar::isUid($permission)) {
            $permission = $this->getPermissionClass()::findById($permission, $guardName);
        }

        if ($permission instanceof Permission) {
            $guardName = $permission->guard_name ?? $guardName;
            $permission = $permission->name;
        }

        if (! is_string($permission)) {
            throw WildcardPermissionInvalidArgument::create();
        }

        return app($this->getWildcardClass(), ['record' => $this])->implies(
            $permission,
            $guardName,
            app(PermissionRegistrar::class)->getWildcardPermissionIndex($this),
        );
    }

    /**
     * An alias to hasPermissionTo(), but avoids throwing an exception.
     *
     * @param  string|int|Permission|BackedEnum  $permission
     */
    public function checkPermissionTo($permission, ?string $guardName = null): bool
    {
        try {
            return $this->hasPermissionTo($permission, $guardName);
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    /**
     * Determine if the model has any of the given permissions.
     *
     * @param  string|int|array|Permission|Collection|BackedEnum  ...$permissions
     */
    public function hasAnyPermission(...$permissions): bool
    {
        $permissions = collect($permissions)->flatten();

        foreach ($permissions as $permission) {
            if ($this->checkPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the model has all of the given permissions.
     *
     * @param  string|int|array|Permission|Collection|BackedEnum  ...$permissions
     */
    public function hasAllPermissions(...$permissions): bool
    {
        $permissions = collect($permissions)->flatten();

        foreach ($permissions as $permission) {
            if (! $this->checkPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the model has, via roles, the given permission.
     */
    protected function hasPermissionViaRole(Permission $permission): bool
    {
        if ($this instanceof Role) {
            return false;
        }

        return $this->hasRole($permission->roles);
    }

    /**
     * Determine if the model has the given permission.
     *
     * @param  string|int|Permission|BackedEnum  $permission
     *
     * @throws PermissionDoesNotExist
     */
    public function hasDirectPermission($permission): bool
    {
        $permission = $this->filterPermission($permission);

        return $this->loadMissing('permissions')->permissions
            ->contains($permission->getKeyName(), $permission->getKey());
    }

    /**
     * Return all the permissions the model has via roles.
     */
    public function getPermissionsViaRoles(): Collection
    {
        if ($this instanceof Role || $this instanceof Permission) {
            return collect();
        }

        return $this->loadMissing('roles', 'roles.permissions')
            ->roles->flatMap(fn ($role) => $role->permissions)
            ->sort()->values();
    }

    /**
     * Return all the permissions the model has, both directly and via roles.
     */
    public function getAllPermissions(): Collection
    {
        /** @var Collection $permissions */
        $permissions = $this->permissions;

        if (! $this instanceof Permission) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }

        return $permissions->sort()->values();
    }

    /**
     * Returns array of permissions ids
     *
     * @param  string|int|array|Permission|Collection|BackedEnum  $permissions
     */
    private function collectPermissions(...$permissions): array
    {
        return collect($permissions)
            ->flatten()
            ->reduce(function ($array, $permission) {
                if (empty($permission)) {
                    return $array;
                }

                $permission = $this->getStoredPermission($permission);
                if (! $permission instanceof Permission) {
                    return $array;
                }

                if (! in_array($permission->getKey(), $array)) {
                    $this->ensureModelSharesGuard($permission);
                    $array[] = $permission->getKey();
                }

                return $array;
            }, []);
    }

    /**
     * Grant the given permission(s) to a role.
     *
     * @param  string|int|array|Permission|Collection|BackedEnum  $permissions
     * @return $this
     */
    public function givePermissionTo(...$permissions): static
    {
        $permissions = $this->collectPermissions($permissions);

        $model = $this->getModel();
        $teamPivot = app(PermissionRegistrar::class)->teams && ! $this instanceof Role ?
            [app(PermissionRegistrar::class)->teamsKey => getPermissionsTeamId()] : [];

        if ($model->exists) {
            $currentPermissions = $this->permissions->map(fn ($permission) => $permission->getKey())->toArray();

            $this->permissions()->attach(array_diff($permissions, $currentPermissions), $teamPivot);
            $model->unsetRelation('permissions');
        } else {
            $class = $model::class;
            $saved = false;

            $class::saved(
                function ($object) use ($permissions, $model, $teamPivot, &$saved) {
                    if ($saved || $model->getKey() != $object->getKey()) {
                        return;
                    }
                    $model->permissions()->attach($permissions, $teamPivot);
                    $model->unsetRelation('permissions');
                    $saved = true;
                }
            );
        }

        if ($this instanceof Role) {
            $this->forgetCachedPermissions();
        }

        if (config('permission.events_enabled')) {
            event(new PermissionAttachedEvent($this->getModel(), $permissions));
        }

        $this->forgetWildcardPermissionIndex();

        return $this;
    }

    public function forgetWildcardPermissionIndex(): void
    {
        app(PermissionRegistrar::class)->forgetWildcardPermissionIndex(
            $this instanceof Role ? null : $this,
        );
    }

    /**
     * Remove all current permissions and set the given ones.
     *
     * @param  string|int|array|Permission|Collection|BackedEnum  $permissions
     * @return $this
     */
    public function syncPermissions(...$permissions): static
    {
        if ($this->getModel()->exists) {
            $this->collectPermissions($permissions);
            $this->permissions()->detach();
            $this->setRelation('permissions', collect());
        }

        return $this->givePermissionTo($permissions);
    }

    /**
     * Revoke the given permission(s).
     *
     * @param  Permission|Permission[]|string|string[]|BackedEnum  $permission
     * @return $this
     */
    public function revokePermissionTo($permission): static
    {
        $storedPermission = $this->getStoredPermission($permission);

        $this->permissions()->detach($storedPermission);

        if ($this instanceof Role) {
            $this->forgetCachedPermissions();
        }

        if (config('permission.events_enabled')) {
            event(new PermissionDetachedEvent($this->getModel(), $storedPermission));
        }

        $this->forgetWildcardPermissionIndex();

        $this->unsetRelation('permissions');

        return $this;
    }

    public function getPermissionNames(): Collection
    {
        return $this->permissions->pluck('name');
    }

    /**
     * @param  string|int|array|Permission|Collection|BackedEnum  $permissions
     * @return Permission|Permission[]|Collection
     */
    protected function getStoredPermission($permissions)
    {
        $permissions = enum_value($permissions);

        if (is_int($permissions) || PermissionRegistrar::isUid($permissions)) {
            return $this->getPermissionClass()::findById($permissions, $this->getDefaultGuardName());
        }

        if (is_string($permissions)) {
            return $this->getPermissionClass()::findByName($permissions, $this->getDefaultGuardName());
        }

        if (is_array($permissions)) {
            $permissions = array_map(fn ($permission) => $permission instanceof Permission ? $permission->name : enum_value($permission), $permissions);

            return $this->getPermissionClass()::whereIn('name', $permissions)
                ->whereIn('guard_name', $this->getGuardNames())
                ->get();
        }

        return $permissions;
    }

    /**
     * @param  Permission|Role  $roleOrPermission
     *
     * @throws GuardDoesNotMatch
     */
    protected function ensureModelSharesGuard($roleOrPermission): void
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
    public function forgetCachedPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Check if the model has All of the requested Direct permissions.
     *
     * @param  string|int|array|Permission|Collection|BackedEnum  ...$permissions
     */
    public function hasAllDirectPermissions(...$permissions): bool
    {
        $permissions = collect($permissions)->flatten();

        foreach ($permissions as $permission) {
            if (! $this->hasDirectPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the model has Any of the requested Direct permissions.
     *
     * @param  string|int|array|Permission|Collection|BackedEnum  ...$permissions
     */
    public function hasAnyDirectPermission(...$permissions): bool
    {
        $permissions = collect($permissions)->flatten();

        foreach ($permissions as $permission) {
            if ($this->hasDirectPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
