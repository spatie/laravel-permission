<?php

namespace Spatie\Permission;

use Illuminate\Database\Eloquent\Collection;

trait PermissionLoaderTrait
{
    /**
     * Array for cache alias
     */
    private function aliasModelFields(array &$keys, array $newKeys = [], array $except = []): void
    {
        $alphas = ! count($keys) ? range('a', 'n') : range('o', 'z');

        foreach ($newKeys as $i => $value) {
            $keys[$value] = $alphas[$i] ?? $value;
        }

        $keys = collect($keys)->except($except)->all();
    }

    /**
     * Changes array keys with alias
     *
     * @return \Illuminate\Support\Collection
     */
    private function aliasedFromArray(array $item, array $alias): \Illuminate\Support\Collection
    {
        return collect($item)->keyBy(function ($value, $key) use ($alias) {
            return $alias[$key] ?? $key;
        });
    }

    /**
     * Load permissions from cache
     * This get cache and turns array into \Illuminate\Database\Eloquent\Collection
     */
    private function loadPermissionsFromCache(): void
    {
        $roleClass = $this->getRoleClass();
        $permissionClass = $this->getPermissionClass();

        $this->permissions = $this->cache->remember(self::$cacheKey, self::$cacheExpirationTime, function () use ($permissionClass) {
            // make the cache smaller using an array with only aliased fields
            $alias = [];
            $roles = [];
            $roleAliasFlag = false;
            $except = config('permission.cache.column_names_except', ['created_at','updated_at', 'deleted_at']);
            $permissions = $permissionClass->with('roles')->get()->map(function ($permission) use (&$roles, &$alias, &$roleAliasFlag, $except) {
                if (! count($alias)) {
                    $this->aliasModelFields($alias, array_merge(array_keys($permission->getAttributes()), ['roles']), $except);
                }

                return $this->aliasedFromArray($permission->getAttributes(), $alias)->except($except)->all() +
                    [
                        $alias['roles'] => $permission->roles->map(function ($role) use (&$roles, &$alias, &$roleAliasFlag, $except) {
                            if (! $roleAliasFlag) {
                                $this->aliasModelFields($alias, array_values(array_diff(array_keys($role->getAttributes()), array_keys($alias))), $except);
                                $roleAliasFlag = true;
                            }
                            $id = $role->getKey();
                            $roles[$id] = $roles[$id] ?? $this->aliasedFromArray($role->getAttributes(), $alias)->except($except)->all();

                            return $id;
                        })->all()
                    ];
            })->all();
            $alias = collect($alias)->except($except)->flip()->except($except)->all();

            return compact('alias', 'roles', 'permissions');
        });
        // temporary, only to allow upgrade from previous versions
        if (! is_array($this->permissions) || ! isset($this->permissions['permissions'])) {
            $this->forgetCachedPermissions();
            $this->loadPermissionsFromCache();

            return;
        }

        $this->permissions['roles'] = array_map(function ($item) use ($roleClass) {
            return (new $roleClass)->newFromBuilder($this->aliasedFromArray($item, $this->permissions['alias'])->all());
        }, $this->permissions['roles']);

        $this->permissions = Collection::make($this->permissions['permissions'])
            ->map(function ($item) use ($permissionClass) {
                $aliased_item = $this->aliasedFromArray($item, $this->permissions['alias']);
                $permission = (new $permissionClass)->newFromBuilder($aliased_item->except('roles')->all());
                $permission->setRelation('roles', Collection::make($aliased_item['roles'])
                    ->map(function ($id) {
                        return $this->permissions['roles'][$id];
                    }));

                return $permission;
            });
    }
}
