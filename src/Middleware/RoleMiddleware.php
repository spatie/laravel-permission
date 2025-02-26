<?php

namespace Spatie\Permission\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Guard;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role, $guard = null)
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

        $roles = is_array($role)
            ? $role
            : explode('|', $role);

        if (! $user->hasAnyRole($roles)) {
            throw UnauthorizedException::forRoles($roles);
        }

        return $next($request);
    }

    /**
     * Specify the role and guard for the middleware.
     *
     * @param  array|string|\BackedEnum  $role
     * @param  string|null  $guard
     * @return string
     */
    public static function using($role, $guard = null)
    {
        // Convert Enum to its value if an Enum is passed
        if ($role instanceof \BackedEnum) {
            $role = $role->value;
        }

        if (is_array($role)) {
            $role = array_map(fn ($r) => $r instanceof \BackedEnum ? $r->value : $r, $role);
            $roleString = implode('|', $role);
        } else {
            $roleString = (string) $role;
        }
        
        $args = is_null($guard) ? $roleString : "$roleString,$guard";

        return static::class.':'.$args;
    }
}
