<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Guards\TokenGuard;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role, $guard = null)
    {
        $authGuard = Auth::guard($guard);

        // For machine-to-machine Passport clients
        $bearerToken = $request->bearerToken();
        if ($bearerToken ) {
            if (!$authGuard instanceof TokenGuard) {
                $authGuard = Auth::guard('api');
            }

            if (method_exists($authGuard, 'client')) {
                $user = $authGuard->client();
            }
        }

        $user = $user ?? $authGuard->user();

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
     * @param  array|string  $role
     * @param  string|null  $guard
     * @return string
     */
    public static function using($role, $guard = null)
    {
        $roleString = is_string($role) ? $role : implode('|', $role);
        $args = is_null($guard) ? $roleString : "$roleString,$guard";

        return static::class.':'.$args;
    }
}
