<?php

namespace Spatie\Permission;

use Log;
use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Cache\Repository;
use Spatie\Permission\Contracts\Permission;

class PermissionRegistrar
{
    /**
     * @var Gate
     */
    protected $gate;

    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @var string
     */
    protected $cacheKey = 'spatie.permission.cache';

    /**
     * @param Gate $gate
     * @param Repository $cache
     */
    public function __construct(Gate $gate, Repository $cache)
    {
        $this->gate = $gate;
        $this->cache = $cache;
    }

    /**
     *  Register the permissions.
     *
     * @return bool
     */
    public function registerPermissions()
    {
        try {
            $this->getPermissions()->map(function ($permission) {
                $this->gate->define($permission->name, function ($user) use ($permission) {
                    return $user->hasPermissionTo($permission);
                });
            });

            return true;
        } catch (Exception $exception) {
            if ($this->shouldLogException()) {
                Log::alert(
                    "Could not register permissions because {$exception->getMessage()}".PHP_EOL
                    .$exception->getTraceAsString());
            }

            return false;
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
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPermissions()
    {
        return $this->cache->rememberForever($this->cacheKey, function () {
            return app(Permission::class)->with('roles')->get();
        });
    }

    /**
     * @return bool
     */
    protected function shouldLogException()
    {
        $logSetting = config('laravel-permission.log_registration_exception');

        if (is_null($logSetting)) {
            return true;
        }

        return $logSetting;
    }
}
