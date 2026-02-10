<?php

namespace Spatie\Permission\Middleware;

use BackedEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Guard;

use function Illuminate\Support\enum_value;

class RoleOrPermissionMiddleware
{
    public function handle(Request $request, Closure $next, $roleOrPermission, ?string $guard = null)
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
     */
    public static function using(array|string|BackedEnum $roleOrPermission, ?string $guard = null): string
    {
        $roleOrPermissionString = self::parseRoleOrPermissionToString($roleOrPermission);
        $args = is_null($guard) ? $roleOrPermissionString : "$roleOrPermissionString,$guard";

        return static::class.':'.$args;
    }

    protected static function parseRoleOrPermissionToString(array|string|BackedEnum $roleOrPermission): string
    {
        $roleOrPermission = enum_value($roleOrPermission);

        if (is_array($roleOrPermission)) {
            return implode('|', array_map(fn ($r) => enum_value($r), $roleOrPermission));
        }

        return (string) $roleOrPermission;
    }
}
