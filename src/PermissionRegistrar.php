<?php

namespace Spatie\Permission;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Cache\Repository;
use Spatie\Permission\Contracts\Permission;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class PermissionRegistrar
{
    /** @var \Illuminate\Contracts\Auth\Access\Gate */
    protected $gate;

    /** @var \Illuminate\Contracts\Cache\Repository */
    protected $cache;

    /** @var string */
    protected $cacheKey = 'spatie.permission.cache';

    public function __construct(Gate $gate, Repository $cache)
    {
        $this->gate = $gate;
        $this->cache = $cache;
    }

    public function registerPermissions(): bool
    {
        $this->gate->before(function (Authorizable $user, string $ability) {
            try {
                if (method_exists($user, 'hasPermissionTo')) {
                    return $user->hasPermissionTo($ability) ?: null;
                }
            } catch (PermissionDoesNotExist $e) {
            }
        });

        return true;
    }

    public function forgetCachedPermissions()
    {
        $this->cache->clear();
    }

    public function getPermissions($params = null): Collection
    {
        return $this->cache->remember($this->cacheKey.($params ? '.'.implode('.', array_values($params)) : ''), config('permission.cache_expiration_time'), function () use ($params) {
            if ($params) {
                return app(Permission::class)->where($params)->with('roles')->get();
            } else {
                return app(Permission::class)->with('roles')->get();
            }
        });
    }
}
