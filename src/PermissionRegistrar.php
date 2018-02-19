<?php

namespace Spatie\Permission;

use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface as Log;
use Spatie\Permission\Contracts\Permission;

class PermissionRegistrar
{
    /** @var \Illuminate\Contracts\Auth\Access\Gate */
    protected $gate;

    /** @var \Illuminate\Contracts\Cache\Repository */
    protected $cache;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /** @var string */
    protected $cacheKey = 'spatie.permission.cache';

    public function __construct(Gate $gate, Repository $cache, Log $logger)
    {
        $this->gate = $gate;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    public function registerPermissions(): bool
    {
        try {
            $this->getPermissions()->map(function ($permission) {
                $this->gate->define($permission->name, function ($user, $restrictable = null) use ($permission) {
                    return $user->hasPermissionTo($permission, $restrictable);
                });
            });

            return true;
        } catch (Exception $exception) {
            if ($this->shouldLogException()) {
                $this->logger->alert(
                    "Could not register permissions because {$exception->getMessage()}" . PHP_EOL .
                    $exception->getTraceAsString()
                );
            }

            return false;
        }
    }

    public function forgetCachedPermissions()
    {
        $this->cache->forget($this->cacheKey);
    }

    // The getPermissions() cache implementation is used via the Permission's findByName() method, which is called by
    // the hasPermissionTo() method (and the similar ones) of HasRoles Trait
    public function getPermissions(): Collection
    {
        return $this->cache->remember($this->cacheKey, config('permission.cache_expiration_time'), function () {
            return app(Permission::class)->with('roles')->get();
        });
    }

    protected function shouldLogException(): bool
    {
        return config('permission.log_registration_exception');
    }
}
