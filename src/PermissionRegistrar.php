<?php

namespace Spatie\Permission;

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;

class PermissionRegistrar
{
    /** @var \Illuminate\Contracts\Cache\Repository */
    protected $cache;

    /** @var \Illuminate\Cache\CacheManager */
    protected $cacheManager;

    /** @var string */
    protected $permissionClass;

    /** @var string */
    protected $roleClass;

    /** @var \Illuminate\Database\Eloquent\Collection */
    protected $permissions;

    /** @var string */
    public static $pivotRole;

    /** @var string */
    public static $pivotPermission;

    /** @var \DateInterval|int */
    public static $cacheExpirationTime;

    /** @var bool */
    public static $teams;

    /** @var string */
    public static $teamsKey;

    /** @var int|string */
    protected $teamId = null;

    /** @var string */
    public static $cacheKey;

    /** @var array */
    private $cachedRoles = [];

    /** @var array */
    private $alias = [];

    /** @var array */
    private $except = [];

    /**
     * PermissionRegistrar constructor.
     *
     * @param  \Illuminate\Cache\CacheManager  $cacheManager
     */
    public function __construct(CacheManager $cacheManager)
    {
        $this->permissionClass = config('permission.models.permission');
        $this->roleClass = config('permission.models.role');

        $this->cacheManager = $cacheManager;
        $this->initializeCache();
    }

    public function initializeCache()
    {
        self::$cacheExpirationTime = config('permission.cache.expiration_time') ?: \DateInterval::createFromDateString('24 hours');

        self::$teams = config('permission.teams', false);
        self::$teamsKey = config('permission.column_names.team_foreign_key');

        self::$cacheKey = config('permission.cache.key');

        self::$pivotRole = config('permission.column_names.role_pivot_key') ?: 'role_id';
        self::$pivotPermission = config('permission.column_names.permission_pivot_key') ?: 'permission_id';

        $this->cache = $this->getCacheStoreFromConfig();
    }

    protected function getCacheStoreFromConfig(): Repository
    {
        // the 'default' fallback here is from the permission.php config file,
        // where 'default' means to use config(cache.default)
        $cacheDriver = config('permission.cache.store', 'default');

        // when 'default' is specified, no action is required since we already have the default instance
        if ($cacheDriver === 'default') {
            return $this->cacheManager->store();
        }

        // if an undefined cache store is specified, fallback to 'array' which is Laravel's closest equiv to 'none'
        if (! \array_key_exists($cacheDriver, config('cache.stores'))) {
            $cacheDriver = 'array';
        }

        return $this->cacheManager->store($cacheDriver);
    }

    /**
     * Set the team id for teams/groups support, this id is used when querying permissions/roles
     *
     * @param  int|string|\Illuminate\Database\Eloquent\Model  $id
     */
    public function setPermissionsTeamId($id)
    {
        if ($id instanceof \Illuminate\Database\Eloquent\Model) {
            $id = $id->getKey();
        }
        $this->teamId = $id;
    }

    /**
     * @return int|string
     */
    public function getPermissionsTeamId()
    {
        return $this->teamId;
    }

    /**
     * Register the permission check method on the gate.
     * We resolve the Gate fresh here, for benefit of long-running instances.
     *
     * @return bool
     */
    public function registerPermissions(): bool
    {
        app(Gate::class)->before(function (Authorizable $user, string $ability) {
            if (method_exists($user, 'checkPermissionTo')) {
                return $user->checkPermissionTo($ability) ?: null;
            }
        });

        return true;
    }

    /**
     * Flush the cache.
     */
    public function forgetCachedPermissions()
    {
        $this->permissions = null;

        return $this->cache->forget(self::$cacheKey);
    }

    /**
     * Clear class permissions.
     * This is only intended to be called by the PermissionServiceProvider on boot,
     * so that long-running instances like Swoole don't keep old data in memory.
     */
    public function clearClassPermissions()
    {
        $this->permissions = null;
    }

    /**
     * Load permissions from cache
     * This get cache and turns array into \Illuminate\Database\Eloquent\Collection
     */
    private function loadPermissions()
    {
        if ($this->permissions) {
            return;
        }

        $this->permissions = $this->cache->remember(self::$cacheKey, self::$cacheExpirationTime, function () {
            return $this->getSerializedPermissionsForCache();
        });

        // fallback for old cache method, must be removed on next mayor version
        if (! isset($this->permissions['alias'])) {
            $this->forgetCachedPermissions();
            $this->loadPermissions();

            return;
        }

        $this->alias = $this->permissions['alias'];

        $this->hydrateRolesCache();

        $this->permissions = $this->getHydratedPermissionCollection();

        $this->cachedRoles = $this->alias = $this->except = [];
    }

    /**
     * Get the permissions based on the passed params.
     *
     * @param  array  $params
     * @param  bool  $onlyOne
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPermissions(array $params = [], bool $onlyOne = false): Collection
    {
        $this->loadPermissions();

        $method = $onlyOne ? 'first' : 'filter';

        $permissions = $this->permissions->$method(static function ($permission) use ($params) {
            foreach ($params as $attr => $value) {
                if ($permission->getAttribute($attr) != $value) {
                    return false;
                }
            }

            return true;
        });

        if ($onlyOne) {
            $permissions = new Collection($permissions ? [$permissions] : []);
        }

        return $permissions;
    }

    /**
     * Get an instance of the permission class.
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public function getPermissionClass(): Permission
    {
        return app($this->permissionClass);
    }

    public function setPermissionClass($permissionClass)
    {
        $this->permissionClass = $permissionClass;
        config()->set('permission.models.permission', $permissionClass);
        app()->bind(Permission::class, $permissionClass);

        return $this;
    }

    /**
     * Get an instance of the role class.
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function getRoleClass(): Role
    {
        return app($this->roleClass);
    }

    public function setRoleClass($roleClass)
    {
        $this->roleClass = $roleClass;
        config()->set('permission.models.role', $roleClass);
        app()->bind(Role::class, $roleClass);

        return $this;
    }

    public function getCacheRepository(): Repository
    {
        return $this->cache;
    }

    public function getCacheStore(): Store
    {
        return $this->cache->getStore();
    }

    protected function getPermissionsWithRoles(): Collection
    {
        return $this->getPermissionClass()->select()->with('roles')->get();
    }

    /**
     * Changes array keys with alias
     *
     * @return array
     */
    private function aliasedArray($model): array
    {
        return collect(is_array($model) ? $model : $model->getAttributes())->except($this->except)
            ->keyBy(function ($value, $key) {
                return $this->alias[$key] ?? $key;
            })->all();
    }

    /**
     * Array for cache alias
     */
    private function aliasModelFields($newKeys = []): void
    {
        $i = 0;
        $alphas = ! count($this->alias) ? range('a', 'h') : range('j', 'p');

        foreach (array_keys($newKeys->getAttributes()) as $value) {
            if (! isset($this->alias[$value])) {
                $this->alias[$value] = $alphas[$i++] ?? $value;
            }
        }

        $this->alias = array_diff_key($this->alias, array_flip($this->except));
    }

    /*
     * Make the cache smaller using an array with only required fields
     */
    private function getSerializedPermissionsForCache()
    {
        $this->except = config('permission.cache.column_names_except', ['created_at', 'updated_at', 'deleted_at']);

        $permissions = $this->getPermissionsWithRoles()
            ->map(function ($permission) {
                if (! $this->alias) {
                    $this->aliasModelFields($permission);
                }

                return $this->aliasedArray($permission) + $this->getSerializedRoleRelation($permission);
            })->all();
        $roles = array_values($this->cachedRoles);
        $this->cachedRoles = [];

        return ['alias' => array_flip($this->alias)] + compact('permissions', 'roles');
    }

    private function getSerializedRoleRelation($permission)
    {
        if (! $permission->roles->count()) {
            return [];
        }

        if (! isset($this->alias['roles'])) {
            $this->alias['roles'] = 'r';
            $this->aliasModelFields($permission->roles[0]);
        }

        return [
            'r' => $permission->roles->map(function ($role) {
                if (! isset($this->cachedRoles[$role->getKey()])) {
                    $this->cachedRoles[$role->getKey()] = $this->aliasedArray($role);
                }

                return $role->getKey();
            })->all(),
        ];
    }

    private function getHydratedPermissionCollection()
    {
        $permissionClass = $this->getPermissionClass();
        $permissionInstance = new $permissionClass();

        return Collection::make(
            array_map(function ($item) use ($permissionInstance) {
                return $permissionInstance
                    ->newFromBuilder($this->aliasedArray(array_diff_key($item, ['r' => 0])))
                    ->setRelation('roles', $this->getHydratedRoleCollection($item['r'] ?? []));
            }, $this->permissions['permissions'])
        );
    }

    private function getHydratedRoleCollection(array $roles)
    {
        return Collection::make(array_values(
            array_intersect_key($this->cachedRoles, array_flip($roles))
        ));
    }

    private function hydrateRolesCache()
    {
        $roleClass = $this->getRoleClass();
        $roleInstance = new $roleClass();

        array_map(function ($item) use ($roleInstance) {
            $role = $roleInstance->newFromBuilder($this->aliasedArray($item));
            $this->cachedRoles[$role->getKey()] = $role;
        }, $this->permissions['roles']);

        $this->permissions['roles'] = [];
    }
}
