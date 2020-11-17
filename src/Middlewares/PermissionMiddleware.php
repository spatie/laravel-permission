<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $permissions = is_array($permission)
            ? $permission
            : explode('|', $permission);

        $driverDatabase = Config::get('database.default', 'mysql');
        Config::set('database.default', Config::get('permission.spatie_database_driver'));

        if ( !Auth::guard($guard)->user()->hasAnyPermission($permissions)) {
            throw UnauthorizedException::forPermissions($permissions);
        }
        Config::set('database.default', $driverDatabase);

        return $next($request);
    }
}
