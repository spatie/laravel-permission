<?php

namespace Spatie\Permission\Traits;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\PermissionRegistrar;

trait RefreshesPermissionCache
{
    public static function bootRefreshesPermissionCache()
    {
        static::saved(function (Model $model) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        static::deleted(function (Model $model) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });
    }
}
