<?php

namespace Spatie\Permission\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Guard;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission, $guard = null)
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

        if (! method_exists($user, 'hasAnyPermission')) {
            throw UnauthorizedException::missingTraitHasRoles($user);
        }

        $permissions = explode('|', self::parsePermissionsToString($permission));

        if (! $user->canAny($permissions)) {
            throw UnauthorizedException::forPermissions($permissions);
        }

        return $next($request);
    }

    /**
     * Specify the permission and guard for the middleware.
     *
     * @param  array|string|\BackedEnum  $permission
     * @param  string|null  $guard
     * @return string
     */
    public static function using($permission, $guard = null)
    {
        // Convert Enum to its value if an Enum is passed
        if ($permission instanceof \BackedEnum) {
            $permission = $permission->value;
        }

        $permissionString = self::parsePermissionsToString($permission);

        $args = is_null($guard) ? $permissionString : "$permissionString,$guard";

        return static::class.':'.$args;
    }

    /**
     * Convert array or string of permissions to string representation.
     *
     * @return string
     */
    protected static function parsePermissionsToString(array|string|\BackedEnum $permission)
    {
        // Convert Enum to its value if an Enum is passed
        if ($permission instanceof \BackedEnum) {
            $permission = $permission->value;
        }

        if (is_array($permission)) {
            $permission = array_map(fn ($r) => $r instanceof \BackedEnum ? $r->value : $r, $permission);

            return implode('|', $permission);
        }

        return (string) $permission;
    }
}
