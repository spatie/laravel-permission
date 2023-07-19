<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\ClientService;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role, $guard = null)
    {
        $authGuard = Auth::guard($guard);

        $roles = is_array($role)
            ? $role
            : explode('|', $role);

        // For machine-to-machine Passport clients
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $client = ClientService::getClient($bearerToken);
            $assignedRoles = ClientService::getClientRoles($client);

            foreach ($roles as $role) {
                if (in_array($role, $assignedRoles)) {
                    return $next($request);
                }
            }
        }

        if ($authGuard->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $user = $authGuard->user();

        if (! method_exists($user, 'hasAnyRole')) {
            throw UnauthorizedException::missingTraitHasRoles($user);
        }

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
