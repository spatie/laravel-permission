<?php

namespace Spatie\Permission;

use Beauty\Modules\Api\Controllers\ApiController;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Contracts\Role;
use Illuminate\Contracts\Auth\Access\Gate;
use Spatie\Permission\Contracts\Permission;
use Illuminate\Contracts\Auth\Access\Authorizable;

class PermissionRegistrar
{
    /** @var \Illuminate\Contracts\Auth\Access\Gate */
    protected $gate;

    /** @var \Illuminate\Contracts\Cache\Repository */
    protected $cache;

    /** @var \Illuminate\Cache\CacheManager */
    protected $cacheManager;

    /** @var string */
    protected $permissionClass;

    /** @var string */
    protected $roleClass;

    /** @var \Illuminate\Support\Collection */
    protected $permissions;

    /** @var DateInterval|int */
    public static $cacheExpirationTime;

    /** @var string */
    public static $cacheKey;

    /** @var string */
    public static $cacheModelKey;

    /**
     * PermissionRegistrar constructor.
     *
     * @param \Illuminate\Contracts\Auth\Access\Gate $gate
     * @param \Illuminate\Cache\CacheManager $cacheManager
     */
    public function __construct(Gate $gate, CacheManager $cacheManager)
    {
        $this->gate = $gate;
        $this->permissionClass = config('permission.models.permission');
        $this->roleClass = config('permission.models.role');

        $this->cacheManager = $cacheManager;
        $this->initializeCache();
    }

    protected function initializeCache()
    {
        self::$cacheExpirationTime = config('permission.cache.expiration_time', config('permission.cache_expiration_time'));

        self::$cacheKey = config('permission.cache.key');
        self::$cacheModelKey = config('permission.cache.model_key');

        $this->cache = $this->getCacheStoreFromConfig();
    }

    protected function getCacheStoreFromConfig(): \Illuminate\Contracts\Cache\Repository
    {
        // the 'default' fallback here is from the permission.php config file, where 'default' means to use config(cache.default)
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
     * Register the permission check method on the gate.
     *
     * @return bool
     */
    public function registerPermissions(): bool
    {
        $this->gate->before(function (Authorizable $user, string $ability) {
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
     * Get the permissions based on the passed params.
     *
     * @param array $params
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPermissions(array $params = []): Collection
    {
        if ($this->permissions === null) {
            $base = resolve(ApiController::class);
            $oProfile = $base->__getProfile(Auth::user());
            $this->permissions = $this->getPermissionClass()->with(['roles' => function ($query) use ($oProfile) {
                $query->where('profileID', $oProfile->profileID);
            }])->get();
        }

//        if ($this->permissions === null) {
//            $this->permissions = $this->cache->remember(self::$cacheKey, self::$cacheExpirationTime, function () {
//                return $this->getPermissionClass()
//                    ->with('roles')
//                    ->get();
//            });
//        }

        $permissions = clone $this->permissions;

        foreach ($params as $attr => $value) {
            $permissions = $permissions->where($attr, $value);
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

    /**
     * Get the instance of the Cache Store.
     *
     * @return \Illuminate\Contracts\Cache\Store
     */
    public function getCacheStore(): \Illuminate\Contracts\Cache\Store
    {
        return $this->cache->getStore();
    }
}
