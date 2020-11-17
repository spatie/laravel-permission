<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleOrPermissionMiddleware
{
    public function handle($request, Closure $next, $roleOrPermission, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }
        $rolesOrPermissions = is_array($roleOrPermission)
            ? $roleOrPermission
            : explode('|', $roleOrPermission);

        $driverDatabase = Config::get('database.default', 'mysql');
        Config::set('database.default', Config::get('permission.spatie_database_driver'));

        if ( !Auth::guard($guard)->user()->hasAnyRole($rolesOrPermissions) && !Auth::guard($guard)->user()->hasAnyPermission($rolesOrPermissions)) {
            throw UnauthorizedException::forRolesOrPermissions($rolesOrPermissions);
        }
        Config::set('database.default', $driverDatabase);

        return $next($request);
    }
}

