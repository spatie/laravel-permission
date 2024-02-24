<?php

namespace Spatie\Permission\Traits;

trait RefreshesPermissionCache
{
    public static function bootRefreshesPermissionCache(): void
    {
        $permissionRegistrar = static::getPermissionRegistrar();

        static::saved(fn () => $permissionRegistrar->forgetCachedPermissions());
        static::deleted(fn () => $permissionRegistrar->forgetCachedPermissions());
    }
}
