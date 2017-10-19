<?php

namespace Spatie\Permission\Traits;

use Spatie\Permission\PermissionRegistrar;

trait RefreshesPermissionCache
{
    public static function bootRefreshesPermissionCache()
    {
        static::updated(function () {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        static::deleted(function () {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });
    }

    /**
     *  Forget the cached permissions.
     */
    public function forgetCachedPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Get the current cached permissions.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected static function getPermissions()
    {
        return app(PermissionRegistrar::class)->getPermissions();
    }
}
