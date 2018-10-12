<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class RoleOrPermissionMiddleware
{
    public function handle($request, Closure $next, $roleOrPermission)
    {
        if (Auth::guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $rolesOrPermissions = is_array($roleOrPermission)
            ? $roleOrPermission
            : explode('|', $roleOrPermission);

        try {
            if (! Auth::user()->hasAnyRole($rolesOrPermissions) || ! Auth::user()->hasAnyPermission($rolesOrPermissions)) {
                throw UnauthorizedException::forRolesOrPermissions($rolesOrPermissions);
            }
        } catch (PermissionDoesNotExist $exception) {
        }

        return $next($request);
    }
}
