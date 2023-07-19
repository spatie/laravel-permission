<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\ClientService;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleOrPermissionMiddleware
{
    public function handle($request, Closure $next, $roleOrPermission, $guard = null)
    {
        $authGuard = Auth::guard($guard);

        $rolesOrPermissions = is_array($roleOrPermission)
            ? $roleOrPermission
            : explode('|', $roleOrPermission);

        // For machine-to-machine Passport clients
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $client = ClientService::getClient($bearerToken);
            $assignedRoles = ClientService::getClientRoles($client);
            $assignedPermissions = ClientService::getClientPermissions($client);
            $assignedRolesAndPermissions = array_merge($assignedRoles, $assignedPermissions);

            foreach($rolesOrPermissions as $roleOrPermission) {
                if (in_array($roleOrPermission, $assignedRolesAndPermissions)) {
                    return $next($request);
                }
            }
        }
        if ($authGuard->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $user = $authGuard->user();

        if (! method_exists($user, 'hasAnyRole') || ! method_exists($user, 'hasAnyPermission')) {
            throw UnauthorizedException::missingTraitHasRoles($user);
        }

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
