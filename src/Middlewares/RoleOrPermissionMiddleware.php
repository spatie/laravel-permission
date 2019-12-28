<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleOrPermissionMiddleware
{
    public function handle($request, Closure $next, $roleOrPermission)
    {
        if (is_string($roleOrPermission)) {  // sample : 'create-admin|super-admin@admin' , here admin is guard name.  create-admin is permission. super-admin is role.  | Notice: guard is optional
            $parsed = explode('@', $roleOrPermission);
            $guard = isset($parsed[1])
                ? $parsed[1]
                : null;
            $rolesOrPermissions = explode('|', $parsed[0]);
        } elseif (is_array($roleOrPermission)) {
            $guard = isset($roleOrPermission['guard']) ? $roleOrPermission['guard'] : null;
            $rolesOrPermissions = $roleOrPermission['roleOrPermission'];
        }
        if (auth($guard)->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        if (!auth($guard)->user()->hasAnyRole($rolesOrPermissions) && !auth($guard)->user()->hasAnyPermission($rolesOrPermissions)) {
        throw UnauthorizedException::forRolesOrPermissions($rolesOrPermissions);
    }

        return $next($request);
    }
}
