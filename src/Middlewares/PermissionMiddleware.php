<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission, $guard = null)
    {
        $authGuard = app('auth')->guard($guard);

        if ($authGuard->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        if (! is_null($permission)) {
            $permissions = is_array($permission)
                ? $permission
                : explode('|', $permission);
        }

        if (is_null($permission)) {
            $permission = $request->route()->getName();

            $permissions = [$permission];
        }

        if ($authGuard->user()->canany($permissions)) {
            return $next($request);
        }

        throw UnauthorizedException::forPermissions($permissions);
    }
}
