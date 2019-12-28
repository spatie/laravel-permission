<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        if (is_string($permission)) {     // sample : 'create-admin|update-admin@admin' , here admin is guard name and create-admin , update-admin are permission. | Notice: guard is optional.
            $parsed = explode('@', $permission);
            $guard = isset($parsed[1])
                ? $parsed[1]
                : null;
            $permissions = explode('|', $parsed[0]);
        } elseif (is_array($permission)) {
            $guard = isset($permission['guard']) ? $permission['guard'] : null;
            $permissions = $permission['permission'];
        }
        if (auth($guard)->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        foreach ($permissions as $permission) {
            if (auth($guard)->user()->can($permission)) {
                return $next($request);
            }
        }

        throw UnauthorizedException::forPermissions($permissions);
    }
}
