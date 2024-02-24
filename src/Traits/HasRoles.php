<?php

namespace Spatie\Permission\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionRegistrar;

trait HasRoles
{
    use HasPermissions;

    public static function bootHasRoles()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $permissionRegistrar = $model::getPermissionRegistrar();

            $teams = $permissionRegistrar->teams;
            $permissionRegistrar->teams = false;
            $model->roles()->detach();
            if (is_a($model, Permission::class)) {
                $model->users()->detach();
            }
            $permissionRegistrar->teams = $teams;
        });
    }

    public static function getPermissionRegistrar(): PermissionRegistrar
    {
        return app(PermissionRegistrar::class);
    }

    /**
     * A model may have multiple roles.
     */
    public function roles(): BelongsToMany
    {
        $permissionRegistrar = static::getPermissionRegistrar();

        $relation = $this->morphToMany(
            $permissionRegistrar->getRoleClass(),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            $permissionRegistrar->pivotRole
        );

        if (! $permissionRegistrar->teams) {
            return $relation;
        }

        $teamField = config('permission.table_names.roles').'.'.$permissionRegistrar->teamsKey;

        return $relation->wherePivot($permissionRegistrar->teamsKey, $permissionRegistrar->getPermissionsTeamId())
            ->where(fn ($q) => $q->whereNull($teamField)->orWhere($teamField, $permissionRegistrar->getPermissionsTeamId()));
    }

    /**
     * Scope the model query to certain roles only.
     *
     * @param  string|int|array|Role|Collection|\BackedEnum  $roles
     * @param  string  $guard
     * @param  bool  $without
     */
    public function scopeRole(Builder $query, $roles, $guard = null, $without = false): Builder
    {
        $permissionRegistrar = static::getPermissionRegistrar();

        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }

        $roles = array_map(function ($role) use ($guard, $permissionRegistrar) {
            if ($role instanceof Role) {
                return $role;
            }

            if ($role instanceof \BackedEnum) {
                $role = $role->value;
            }

            $method = is_int($role) || $permissionRegistrar::isUid($role) ? 'findById' : 'findByName';

            return $permissionRegistrar->getRoleClass()::{$method}($role, $guard ?: $this->getDefaultGuardName());
        }, Arr::wrap($roles));

        $key = (new ($permissionRegistrar->getRoleClass())())->getKeyName();

        return $query->{! $without ? 'whereHas' : 'whereDoesntHave'}('roles', fn (Builder $subQuery) => $subQuery
            ->whereIn(config('permission.table_names.roles').".$key", \array_column($roles, $key))
        );
    }

    /**
     * Scope the model query to only those without certain roles.
     *
     * @param  string|int|array|Role|Collection|\BackedEnum  $roles
     * @param  string  $guard
     */
    public function scopeWithoutRole(Builder $query, $roles, $guard = null): Builder
    {
        return $this->scopeRole($query, $roles, $guard, true);
    }

    /**
     * Returns roles ids as array keys
     *
     * @param  string|int|array|Role|Collection|\BackedEnum  $roles
     */
    private function collectRoles(...$roles): array
    {
        return collect($roles)
            ->flatten()
            ->reduce(function ($array, $role) {
                if (empty($role)) {
                    return $array;
                }

                $role = $this->getStoredRole($role);
                if (! $role instanceof Role) {
                    return $array;
                }

                if (! in_array($role->getKey(), $array)) {
                    $this->ensureModelSharesGuard($role);
                    $array[] = $role->getKey();
                }

                return $array;
            }, []);
    }

    /**
     * Assign the given role to the model.
     *
     * @param  string|int|array|Role|Collection|\BackedEnum  ...$roles
     * @return $this
     */
    public function assignRole(...$roles)
    {
        $permissionRegistrar = static::getPermissionRegistrar();
        $roles = $this->collectRoles($roles);

        $model = $this->getModel();
        $teamPivot = $permissionRegistrar->teams && ! is_a($this, Permission::class) ?
            [$permissionRegistrar->teamsKey => $permissionRegistrar->getPermissionsTeamId()] : [];

        if ($model->exists) {
            $currentRoles = $this->roles->map(fn ($role) => $role->getKey())->toArray();

            $this->roles()->attach(array_diff($roles, $currentRoles), $teamPivot);
            $model->unsetRelation('roles');
        } else {
            $class = \get_class($model);

            $class::saved(
                function ($object) use ($roles, $model, $teamPivot) {
                    if ($model->getKey() != $object->getKey()) {
                        return;
                    }
                    $model->roles()->attach($roles, $teamPivot);
                    $model->unsetRelation('roles');
                }
            );
        }

        if (is_a($this, Permission::class)) {
            $this->forgetCachedPermissions();
        }

        return $this;
    }

    /**
     * Revoke the given role from the model.
     *
     * @param  string|int|Role|\BackedEnum  $role
     */
    public function removeRole($role)
    {
        $this->roles()->detach($this->getStoredRole($role));

        $this->unsetRelation('roles');

        if (is_a($this, Permission::class)) {
            $this->forgetCachedPermissions();
        }

        return $this;
    }

    /**
     * Remove all current roles and set the given ones.
     *
     * @param  string|int|array|Role|Collection|\BackedEnum  ...$roles
     * @return $this
     */
    public function syncRoles(...$roles)
    {
        if ($this->getModel()->exists) {
            $this->collectRoles($roles);
            $this->roles()->detach();
            $this->setRelation('roles', collect());
        }

        return $this->assignRole($roles);
    }

    /**
     * Determine if the model has (one of) the given role(s).
     *
     * @param  string|int|array|Role|Collection|\BackedEnum  $roles
     */
    public function hasRole($roles, ?string $guard = null): bool
    {
        $permissionRegistrar = static::getPermissionRegistrar();
        $this->loadMissing('roles');

        if (is_string($roles) && strpos($roles, '|') !== false) {
            $roles = $this->convertPipeToArray($roles);
        }

        if ($roles instanceof \BackedEnum) {
            $roles = $roles->value;
        }

        if (is_int($roles) || $permissionRegistrar::isUid($roles)) {
            $key = (new ($permissionRegistrar->getRoleClass())())->getKeyName();

            return $guard
                ? $this->roles->where('guard_name', $guard)->contains($key, $roles)
                : $this->roles->contains($key, $roles);
        }

        if (is_string($roles)) {
            return $guard
                ? $this->roles->where('guard_name', $guard)->contains('name', $roles)
                : $this->roles->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains($roles->getKeyName(), $roles->getKey());
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role, $guard)) {
                    return true;
                }
            }

            return false;
        }

        if ($roles instanceof Collection) {
            return $roles->intersect($guard ? $this->roles->where('guard_name', $guard) : $this->roles)->isNotEmpty();
        }

        throw new \TypeError('Unsupported type for $roles parameter to hasRole().');
    }

    /**
     * Determine if the model has any of the given role(s).
     *
     * Alias to hasRole() but without Guard controls
     *
     * @param  string|int|array|Role|Collection|\BackedEnum  $roles
     */
    public function hasAnyRole(...$roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if the model has all of the given role(s).
     *
     * @param  string|array|Role|Collection|\BackedEnum  $roles
     */
    public function hasAllRoles($roles, ?string $guard = null): bool
    {
        $this->loadMissing('roles');

        if ($roles instanceof \BackedEnum) {
            $roles = $roles->value;
        }

        if (is_string($roles) && strpos($roles, '|') !== false) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            return $guard
                ? $this->roles->where('guard_name', $guard)->contains('name', $roles)
                : $this->roles->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains($roles->getKeyName(), $roles->getKey());
        }

        $roles = collect()->make($roles)->map(function ($role) {
            if ($role instanceof \BackedEnum) {
                return $role->value;
            }

            return $role instanceof Role ? $role->name : $role;
        });

        return $roles->intersect(
            $guard
                ? $this->roles->where('guard_name', $guard)->pluck('name')
                : $this->getRoleNames()
        ) == $roles;
    }

    /**
     * Determine if the model has exactly all of the given role(s).
     *
     * @param  string|array|Role|Collection  $roles
     */
    public function hasExactRoles($roles, ?string $guard = null): bool
    {
        $this->loadMissing('roles');

        if (is_string($roles) && strpos($roles, '|') !== false) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            $roles = [$roles];
        }

        if ($roles instanceof Role) {
            $roles = [$roles->name];
        }

        $roles = collect()->make($roles)->map(fn ($role) => $role instanceof Role ? $role->name : $role
        );

        return $this->roles->count() == $roles->count() && $this->hasAllRoles($roles, $guard);
    }

    /**
     * Return all permissions directly coupled to the model.
     */
    public function getDirectPermissions(): Collection
    {
        return $this->permissions;
    }

    public function getRoleNames(): Collection
    {
        $this->loadMissing('roles');

        return $this->roles->pluck('name');
    }

    protected function getStoredRole($role): Role
    {
        $permissionRegistrar = static::getPermissionRegistrar();

        if ($role instanceof \BackedEnum) {
            $role = $role->value;
        }

        if (is_int($role) || $permissionRegistrar::isUid($role)) {
            return $permissionRegistrar->getRoleClass()::findById($role, $this->getDefaultGuardName());
        }

        if (is_string($role)) {
            return $permissionRegistrar->getRoleClass()::findByName($role, $this->getDefaultGuardName());
        }

        return $role;
    }

    protected function convertPipeToArray(string $pipeString)
    {
        $pipeString = trim($pipeString);

        if (strlen($pipeString) <= 2) {
            return [str_replace('|', '', $pipeString)];
        }

        $quoteCharacter = substr($pipeString, 0, 1);
        $endCharacter = substr($quoteCharacter, -1, 1);

        if ($quoteCharacter !== $endCharacter) {
            return explode('|', $pipeString);
        }

        if (! in_array($quoteCharacter, ["'", '"'])) {
            return explode('|', $pipeString);
        }

        return explode('|', trim($pipeString, $quoteCharacter));
    }
}
