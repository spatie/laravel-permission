<?php

namespace Spatie\Permission;

trait RefreshesPermissionCache {

    public static function bootRefreshesPermissionCache()
    {
        static::created(function($model) {
            $model->forgetCachedPermissions();
        });

        static::updated(function($model) {
            $model->forgetCachedPermissions();
        });

        static::deleted(function($model) {
            $model->forgetCachedPermissions();
        });
    }

    public function forgetCachedPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
