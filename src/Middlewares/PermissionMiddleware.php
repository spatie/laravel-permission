<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\ClientService;
use Spatie\Permission\Exceptions\UnauthorizedException;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission, $guard = null)
    {
        $authGuard = Auth::guard($guard);

        $permissions = is_array($permission)
            ? $permission
            : explode('|', $permission);

        // For machine-to-machine Passport clients
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $client = ClientService::getClient($bearerToken);
            $assignedPermissions = ClientService::getClientPermissions($client);

            foreach ($permissions as $permission) {
                if (in_array($permission, $assignedPermissions)) {
                    return $next($request);
                }
            }
        }

        if ($authGuard->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $user = $authGuard->user();

        if (! method_exists($user, 'hasAnyPermission')) {
            throw UnauthorizedException::missingTraitHasRoles($user);
        }

        if (! $user->canAny($permissions)) {
            throw UnauthorizedException::forPermissions($permissions);
        }

        return $next($request);
    }

    /**
     * Specify the permission and guard for the middleware.
     *
     * @param  array|string  $permission
     * @param  string|null  $guard
     * @return string
     */
    public static function using($permission, $guard = null)
    {
        $permissionString = is_string($permission) ? $permission : implode('|', $permission);
        $args = is_null($guard) ? $permissionString : "$permissionString,$guard";

        return static::class.':'.$args;
    }
}
