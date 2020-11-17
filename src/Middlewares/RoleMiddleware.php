<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role, $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $roles = is_array($role)
            ? $role
            : explode('|', $role);

        $driverDatabase = Config::get('database.default', 'mysql');
        Config::set('database.default', Config::get('permission.spatie_database_driver'));

        if ( !Auth::guard($guard)->user()->hasAnyRole($roles)) {
            throw UnauthorizedException::forRoles($roles);
        }

        Config::set('database.default', $driverDatabase);

        return $next($request);
    }
}

