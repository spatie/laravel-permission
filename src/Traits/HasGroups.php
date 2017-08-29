<?php

namespace Spatie\Permission\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Contracts\Group;
use Spatie\Permission\Contracts\Permission;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasGroups
{
    use HasRoles;

    public static function bootHasGroups()
    {
        static::deleting(function ($model) {
            $model->groups()->detach();
            $model->roles()->detach();
            $model->permissions()->detach();
        });
    }

    /**
     * A model may have multiple groups.
     */
    public function groups(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.group'),
            'model',
            config('permission.table_names.model_has_groups'),
            'model_id',
            'group_id'
        );
    }

    /**
     * Scope the model query to certain groups only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array|\Spatie\Permission\Contracts\Group|\Illuminate\Support\Collection $groups
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGroup(Builder $query, $groups): Builder
    {
        if ($groups instanceof Collection) {
            $groups = $groups->toArray();
        }

        if (! is_array($groups)) {
            $groups = [$groups];
        }

        $groups = array_map(function ($group) {
            if ($group instanceof Group) {
                return $group;
            }

            return app(Group::class)->findByName($group, $this->getDefaultGuardName());
        }, $groups);

        return $query->whereHas('groups', function ($query) use ($groups) {
            $query->where(function ($query) use ($groups) {
                foreach ($groups as $group) {
                    $query->orWhere(config('permission.table_names.groups').'.id', $group->id);
                }
            });
        });
    }

    /**
     * Assign the given group to the model.
     *
     * @param array|string|\Spatie\Permission\Contracts\Group ...$groups
     *
     * @return $this
     */
    public function assignGroup(...$groups)
    {
        $groups = collect($groups)
            ->flatten()
            ->map(function ($group) {
                return $this->getStoredGroup($group);
            })
            ->each(function ($group) {
                $this->ensureModelSharesGuard($group);
            })
            ->all();

        $this->groups()->saveMany($groups);

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Revoke the given group from the model.
     *
     * @param string|\Spatie\Permission\Contracts\Group ...$group
     */
    public function removeGroup($group)
    {
        $this->groups()->detach($this->getStoredGroup($group));
    }

    /**
     * Remove all current groups and set the given ones.
     *
     * @param array|\Spatie\Permission\Contracts\Group ...$groups
     *
     * @return $this
     */
    public function syncGroups(...$groups)
    {
        $this->groups()->detach();

        return $this->assignGroup($groups);
    }

    /**
     * Determine if the model has (one of) the given group(s).
     *
     * @param string|array|\Spatie\Permission\Contracts\Group|\Illuminate\Support\Collection $groups
     *
     * @return bool
     */
    public function hasGroup($groups): bool
    {
        if (is_string($groups) && false !== strpos($groups, '|')) {
            $groups = $this->convertPipeToArray($groups);
        }

        if (is_string($groups)) {
            return $this->groups->contains('name', $groups);
        }

        if ($groups instanceof Group) {
            return $this->groups->contains('id', $groups->id);
        }

        if (is_array($groups)) {
            foreach ($groups as $group) {
                if ($this->hasGroup($group)) {
                    return true;
                }
            }

            return false;
        }

        return $groups->intersect($this->groups)->isNotEmpty();
    }

    /**
     * Determine if the model has any of the given group(s).
     *
     * @param string|array|\Spatie\Permission\Contracts\Group|\Illuminate\Support\Collection $groups
     *
     * @return bool
     */
    public function hasAnyGroup($groups): bool
    {
        return $this->hasGroup($groups);
    }

    /**
     * Determine if the model has all of the given group(s).
     *
     * @param string|\Spatie\Permission\Contracts\Group|\Illuminate\Support\Collection $groups
     *
     * @return bool
     */
    public function hasAllGroups($groups): bool
    {
        if (is_string($groups) && false !== strpos($groups, '|')) {
            $groups = $this->convertPipeToArray($groups);
        }

        if (is_string($groups)) {
            return $this->groups->contains('name', $groups);
        }

        if ($groups instanceof Group) {
            return $this->groups->contains('id', $groups->id);
        }

        $groups = collect()->make($groups)->map(function ($group) {
            return $group instanceof Group ? $group->name : $group;
        });

        return $groups->intersect($this->groups->pluck('name')) == $groups;
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

        return $this->hasDirectPermission($permission)
            || $this->hasPermissionViaRole($permission)
            || $this->hasPermissionViaGroup($permission)
            || $this->hasPermissionViaGroupRoles($permission);
    }

    /**
     * Determine if the model has, via groups, the given permission.
     *
     * @param \Spatie\Permission\Contracts\Permission $permission
     *
     * @return bool
     */
    protected function hasPermissionViaGroup(Permission $permission): bool
    {

        return $this->hasGroup($permission->groups);
    }

    /**
     * Determine if the model has, via roles, the given permission.
     *
     * @param \Spatie\Permission\Contracts\Permission $permission
     *
     * @return bool
     */
    protected function hasPermissionViaGroupRoles(Permission $permission): bool
    {
        foreach ($this->groups as $group)
        {
            foreach ($group->roles as $role)
            {
                if ($role->permissions->contains('id', $permission->id))
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return all the permissions the model has via groups.
     */
    public function getPermissionsViaGroups(): Collection
    {
        return $this->load('groups', 'groups.permissions')
            ->groups->flatMap(function ($group) {
                return $group->permissions;
            })->sort()->values();
    }

    /**
     * Return all the permissions the model has via roles in groups.
     */
    public function getPermissionsViaGroupRoles(): Collection
    {
        $c = new Collection();
        foreach ($this->groups as $group)
        {
            foreach ($group->roles as $role)
            {
                $p = $role->load('roles', 'roles.permissions')
                    ->roles->flatMap(function ($role) {
                        return $role->permissions;
                    });
                $c->merge($p);
            }
        }

        return $c->sort()->values();
    }

    /**
     * Return all the permissions the model has, both directly, via roles, via groups and via roles in groups.
     */
    public function getAllPermissions(): Collection
    {
        return $this->permissions
            ->merge($this->getPermissionsViaRoles())
            ->merge($this->getPermissionsViaGroups())
            ->merge($this->getPermissionsViaGroupRoles())
            ->sort()
            ->values();
    }

    protected function getStoredGroup($group): Group
    {
        if (is_string($group)) {
            return app(Group::class)->findByName($group, $this->getDefaultGuardName());
        }

        return $group;
    }
}
