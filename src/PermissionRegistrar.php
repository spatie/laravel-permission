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

    /** @var string */
    protected $permissionClass;

    /** @var string */
    protected $roleClass;

    public function __construct(Gate $gate, Repository $cache)
    {
        $this->gate = $gate;
        $this->cache = $cache;
        $this->permissionClass = config('permission.models.permission');
        $this->roleClass = config('permission.models.role');
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
        $this->cache->tags($this->cacheKey)->flush();
    }

    public function getPermissions($params = null): Collection
    {
        $permissionClass = $this->getPermissionClass();

        return $this->cache->tags($this->cacheKey)->remember($this->cacheKey.($params ? '.'.implode('.', array_values($params)) : ''), config('permission.cache_expiration_time'), function () use ($permissionClass, $params) {
            if ($params) {
                return $permissionClass->where($params)->with('roles')->get();
            } else {
                return $permissionClass->with('roles')->get();
            }
        });
    }

    public function getPermissionClass()
    {
        return app($this->permissionClass);
    }

    public function getRoleClass()
    {
        return app($this->roleClass);
    }
}
