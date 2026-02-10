<?php

namespace Spatie\Permission\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Guard;

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
    public static function using(array|string|\BackedEnum $role, ?string $guard = null): string
    {
        $roleString = self::parseRolesToString($role);

        $args = is_null($guard) ? $roleString : "$roleString,$guard";

        return static::class.':'.$args;
    }

    protected static function parseRolesToString(array|string|\BackedEnum $role): string
    {
        // Convert Enum to its value if an Enum is passed
        if ($role instanceof \BackedEnum) {
            $role = $role->value;
        }

        if (is_array($role)) {
            $role = array_map(fn ($r) => $r instanceof \BackedEnum ? $r->value : $r, $role);

            return implode('|', $role);
        }

        return (string) $role;
    }
}
