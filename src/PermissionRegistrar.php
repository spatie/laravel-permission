<?php

namespace Spatie\Permission;

use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Cache\Repository;
use Log;
use Spatie\Permission\Models\Permission;

class PermissionRegistrar
{
    /**
     * @var Gate
     */
    protected $gate;
    /**
     * @var Repository
     */
    private $cache;

    private $cacheKey = 'spatie.permission.cache';

    public function __construct(Gate $gate, Repository $cache)
    {
        $this->gate = $gate;
        $this->cache = $cache;
    }

    /**
     *  Register the permissions.
     */
    public function registerPermissions()
    {
        try {
            foreach ($this->getPermissions() as $permission) {
                $this->gate->define($permission->name, function ($user) use ($permission) {
                    return $user->hasRole($permission->roles);
                });
            }
        } catch (Exception $e) {
            Log::alert('Could not register permissions');
        }
    }

    /**
     *  Forget the cached permissions.
     */
    public function forgetCachedPermissions()
    {
        $this->cache->forget($this->cacheKey);
    }

    /**
     * Get the current permissions.
     *
     * @return mixed
     */
    protected function getPermissions()
    {
        return $this->cache->rememberForever($this->cacheKey, function () {
            return Permission::with('roles')->get();
        });
    }
}
