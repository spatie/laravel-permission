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

        $rolesOrPermissions = explode('|', self::parseRoleOrPermissionToString($roleOrPermission));

        if (! $user->canAny($rolesOrPermissions) && ! $user->hasAnyRole($rolesOrPermissions)) {
            throw UnauthorizedException::forRolesOrPermissions($rolesOrPermissions);
        }

        return $next($request);
    }

    /**
     * Specify the role or permission and guard for the middleware.
     *
     * @param  array|string|\BackedEnum  $roleOrPermission
     * @param  string|null  $guard
     * @return string
     */
    public static function using($roleOrPermission, $guard = null)
    {
        $roleOrPermissionString = self::parseRoleOrPermissionToString($roleOrPermission);
        $args = is_null($guard) ? $roleOrPermissionString : "$roleOrPermissionString,$guard";

        return static::class.':'.$args;
    }

    /**
     * Convert array or string of roles/permissions to string representation.
     *
     * @return string
     */
    protected static function parseRoleOrPermissionToString(array|string|\BackedEnum $roleOrPermission)
    {
        // Convert Enum to its value if an Enum is passed
        if ($roleOrPermission instanceof \BackedEnum) {
            $roleOrPermission = $roleOrPermission->value;
        }

        if (is_array($roleOrPermission)) {
            $roleOrPermission = array_map(fn ($r) => $r instanceof \BackedEnum ? $r->value : $r, $roleOrPermission);

            return implode('|', $roleOrPermission);
        }

        return (string) $roleOrPermission;
    }
}
