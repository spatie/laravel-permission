<?php

namespace Spatie\Permission\Middleware;

use BackedEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Guard;

use function Illuminate\Support\enum_value;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role, ?string $guard = null)
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

        if (! method_exists($user, 'hasAnyRole')) {
            throw UnauthorizedException::missingTraitHasRoles($user);
        }

        $roles = explode('|', self::parseRolesToString($role));

        if (! $user->hasAnyRole($roles)) {
            throw UnauthorizedException::forRoles($roles);
        }

        return $next($request);
    }

    /**
     * Specify the role and guard for the middleware.
     */
    public static function using(array|string|BackedEnum $role, ?string $guard = null): string
    {
        $roleString = self::parseRolesToString($role);

        $args = is_null($guard) ? $roleString : "$roleString,$guard";

        return static::class.':'.$args;
    }

    protected static function parseRolesToString(array|string|BackedEnum $role): string
    {
        $role = enum_value($role);

        if (is_array($role)) {
            return implode('|', array_map(fn ($r) => enum_value($r), $role));
        }

        return (string) $role;
    }
}
