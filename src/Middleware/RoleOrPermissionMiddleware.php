<?php

namespace Spatie\Permission\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Guard;

class RoleOrPermissionMiddleware
{
    public function handle($request, Closure $next, $roleOrPermission, $guard = null)
    {
        $authGuard = Auth::guard($guard);

        $user = $authGuard->user();

        // For machine-to-machine Passport clients
        if (! $user && $request->bearerToken() && config('permission.use_passport_client_credentials')) {
            $user = Guard::getPassportClient($guard);
        }

        if (! $user) {
            throw UnauthorizedException::notLoggedIn();
        }

        if (! method_exists($user, 'hasAnyRole') || ! method_exists($user, 'hasAnyPermission')) {
            throw UnauthorizedException::missingTraitHasRoles($user);
        }

        $rolesOrPermissions = is_array($roleOrPermission)
            ? $roleOrPermission
            : explode('|', $roleOrPermission);

        if (! $user->canAny($rolesOrPermissions) && ! $user->hasAnyRole($rolesOrPermissions)) {
            throw UnauthorizedException::forRolesOrPermissions($rolesOrPermissions);
        }

        return $next($request);
    }

    /**
     * Specify the role or permission and guard for the middleware.
     *
     * @param  array|string  $roleOrPermission
     * @param  string|null  $guard
     * @return string
     */
    public static function using($roleOrPermission, $guard = null)
    {
        $roleOrPermissionString = is_string($roleOrPermission) ? $roleOrPermission : implode('|', $roleOrPermission);
        $args = is_null($guard) ? $roleOrPermissionString : "$roleOrPermissionString,$guard";

        return static::class.':'.$args;
    }
}
