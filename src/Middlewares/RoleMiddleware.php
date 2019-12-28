<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        if (is_string($role)) {  // sample : 'support|super-admin@admin' , here admin is guard name and support , super-admin are role .   | Notice: guard is optional.
            $parsed = explode('@', $role);
            $guard = isset($parsed[1])
                ? $parsed[1]
                : null;
            $roles = explode('|', $parsed[0]);
        } elseif (is_array($role)) {
            $guard = isset($role['guard']) ?
                $role['guard'] : null;
            $roles = $role['role'];
        }
        if (auth($guard)->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        if (!auth($guard)->user()->hasAnyRole($roles)) {
            throw UnauthorizedException::forRoles($roles);
        }

        return $next($request);
    }
}
