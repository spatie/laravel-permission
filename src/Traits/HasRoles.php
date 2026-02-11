<?php

namespace Spatie\Permission\Traits;

use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Events\RoleAttachedEvent;
use Spatie\Permission\Events\RoleDetachedEvent;
use Spatie\Permission\PermissionRegistrar;
use TypeError;

use function Illuminate\Support\enum_value;

trait HasRoles
{
    use HasPermissions;

    private ?string $roleClass = null;

    public static function bootHasRoles(): void
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $teams = app(PermissionRegistrar::class)->teams;
            app(PermissionRegistrar::class)->teams = false;
            $model->roles()->detach();
            if ($model instanceof Permission) {
                $model->users()->detach();
            }
            app(PermissionRegistrar::class)->teams = $teams;
        });
    }

    public function getRoleClass(): string
    {
        if (! $this->roleClass) {
            $this->roleClass = app(PermissionRegistrar::class)->getRoleClass();
        }

        return $this->roleClass;
    }

    /**
     * A model may have multiple roles.
     */
    public function roles(): BelongsToMany
    {
        $relation = $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            app(PermissionRegistrar::class)->pivotRole
        );

        if (! app(PermissionRegistrar::class)->teams) {
            return $relation;
        }

        $teamsKey = app(PermissionRegistrar::class)->teamsKey;
        $relation->withPivot($teamsKey);
        $teamField = config('permission.table_names.roles').'.'.$teamsKey;

        return $relation->wherePivot($teamsKey, getPermissionsTeamId())
            ->where(fn ($q) => $q->whereNull($teamField)->orWhere($teamField, getPermissionsTeamId()));
    }

    /**
     * Scope the model query to certain roles only.
     *
     * @param  string|int|array|Role|Collection|BackedEnum  $roles
     */
    public function scopeRole(Builder $query, $roles, ?string $guard = null, bool $without = false): Builder
    {
        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }

        $roles = array_map(function ($role) use ($guard) {
            if ($role instanceof Role) {
                return $role;
            }

            $role = enum_value($role);

            $method = is_int($role) || PermissionRegistrar::isUid($role) ? 'findById' : 'findByName';

            return $this->getRoleClass()::{$method}($role, $guard ?: $this->getDefaultGuardName());
        }, Arr::wrap($roles));

        $key = (new ($this->getRoleClass())())->getKeyName();

        return $query->{! $without ? 'whereHas' : 'whereDoesntHave'}('roles', fn (Builder $subQuery) => $subQuery
            ->whereIn(config('permission.table_names.roles').".$key", array_column($roles, $key))
        );
    }

    /**
     * Scope the model query to only those without certain roles.
     *
     * @param  string|int|array|Role|Collection|BackedEnum  $roles
     */
    public function scopeWithoutRole(Builder $query, $roles, ?string $guard = null): Builder
    {
        return $this->scopeRole($query, $roles, $guard, true);
    }

    /**
     * Returns array of role ids
     *
     * @param  string|int|array|Role|Collection|BackedEnum  $roles
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
     * @param  string|int|array|Role|Collection|BackedEnum  ...$roles
     * @return $this
     */
    public function assignRole(...$roles): static
    {
        $roles = $this->collectRoles($roles);

        $model = $this->getModel();
        $teamPivot = app(PermissionRegistrar::class)->teams && ! $this instanceof Permission ?
            [app(PermissionRegistrar::class)->teamsKey => getPermissionsTeamId()] : [];

        if ($model->exists) {
            if (app(PermissionRegistrar::class)->teams) {
                // explicit reload in case team has been changed since last load
                $this->load('roles');
            }

            $currentRoles = $this->roles->map(fn ($role) => $role->getKey())->toArray();

            $this->roles()->attach(array_diff($roles, $currentRoles), $teamPivot);
            $model->unsetRelation('roles');
        } else {
            $class = $model::class;
            $saved = false;

            $class::saved(
                function ($object) use ($roles, $model, $teamPivot, &$saved) {
                    if ($saved || $model->getKey() != $object->getKey()) {
                        return;
                    }
                    $model->roles()->attach($roles, $teamPivot);
                    $model->unsetRelation('roles');
                    $saved = true;
                }
            );
        }

        if ($this instanceof Permission) {
            $this->forgetCachedPermissions();
        }

        if (config('permission.events_enabled')) {
            event(new RoleAttachedEvent($this->getModel(), $roles));
        }

        return $this;
    }

    /**
     * Revoke the given role from the model.
     *
     * @param  string|int|array|Role|Collection|BackedEnum  ...$role
     * @return $this
     */
    public function removeRole(...$role): static
    {
        $roles = $this->collectRoles($role);

        $this->roles()->detach($roles);

        $this->unsetRelation('roles');

        if ($this instanceof Permission) {
            $this->forgetCachedPermissions();
        }

        if (config('permission.events_enabled')) {
            event(new RoleDetachedEvent($this->getModel(), $roles));
        }

        return $this;
    }

    /**
     * Remove all current roles and set the given ones.
     *
     * @param  string|int|array|Role|Collection|BackedEnum  ...$roles
     * @return $this
     */
    public function syncRoles(...$roles): static
    {
        if ($this->getModel()->exists) {
            $this->collectRoles($roles);
            if (config('permission.events_enabled')) {
                $currentRoles = $this->roles()->get();
                if ($currentRoles->isNotEmpty()) {
                    $this->removeRole($currentRoles);
                }
            } else {
                $this->roles()->detach();
                $this->setRelation('roles', collect());
            }
        }

        return $this->assignRole($roles);
    }

    /**
     * Determine if the model has (one of) the given role(s).
     *
     * @param  string|int|array|Role|Collection|BackedEnum  $roles
     */
    public function hasRole($roles, ?string $guard = null): bool
    {
        $this->loadMissing('roles');

        if (is_string($roles) && str_contains($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if ($roles instanceof BackedEnum) {
            $roles = $roles->value;

            return $this->roles
                ->when($guard, fn ($q) => $q->where('guard_name', $guard))
                ->pluck('name')
                ->contains(fn ($name) => enum_value($name) == $roles);
        }

        if (is_int($roles) || PermissionRegistrar::isUid($roles)) {
            $key = (new ($this->getRoleClass())())->getKeyName();

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

        throw new TypeError('Unsupported type for $roles parameter to hasRole().');
    }

    /**
     * Determine if the model has any of the given role(s).
     *
     * Alias to hasRole() but without Guard controls
     *
     * @param  string|int|array|Role|Collection|BackedEnum  $roles
     */
    public function hasAnyRole(...$roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if the model has all of the given role(s).
     *
     * @param  string|array|Role|Collection|BackedEnum  $roles
     */
    public function hasAllRoles($roles, ?string $guard = null): bool
    {
        $this->loadMissing('roles');

        $roles = enum_value($roles);

        if (is_string($roles) && str_contains($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            return $this->hasRole($roles, $guard);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains($roles->getKeyName(), $roles->getKey());
        }

        $roles = collect()->make($roles)->map(fn ($role) => $role instanceof Role ? $role->name : enum_value($role));

        $roleNames = $guard
            ? $this->roles->where('guard_name', $guard)->pluck('name')
            : $this->getRoleNames();

        $roleNames = $roleNames->transform(fn ($roleName) => enum_value($roleName));

        return $roles->intersect($roleNames) == $roles;
    }

    /**
     * Determine if the model has exactly all of the given role(s).
     *
     * @param  string|array|Role|Collection|BackedEnum  $roles
     */
    public function hasExactRoles($roles, ?string $guard = null): bool
    {
        $this->loadMissing('roles');

        if (is_string($roles) && str_contains($roles, '|')) {
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
        $role = enum_value($role);

        if (is_int($role) || PermissionRegistrar::isUid($role)) {
            return $this->getRoleClass()::findById($role, $this->getDefaultGuardName());
        }

        if (is_string($role)) {
            return $this->getRoleClass()::findByName($role, $this->getDefaultGuardName());
        }

        return $role;
    }

    protected function convertPipeToArray(string $pipeString): array
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
