<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Spatie\Permission\Exceptions\UnauthorizedException;

class CascadePermissionMiddleware
{
    /**
     * Do a cascading permissions check by recreating the permission namespace tier-by-tier.
     *
     * example:
     * admin.auth.users.modify.create
     *
     * checks the permissions in the following dot-notation-nested order to find first match
     * admin
     * admin.auth
     * admin.auth.users
     * admin.auth.users.modify
     * admin.auth.users.modify.create
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission, $guard = null)
    {
        if (is_null($guard)) {
            $guard = config('auth.defaults.guard');
        }

        //guests are not allowed
        if (app('auth')->guard($guard)->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $permissions = is_array($permission) ? $permission : explode('|', $permission);

        foreach ($permissions as $permission) {

            // split elements using dot-notation
            $parts = explode('.', $permission);
            $ability = '';

            foreach ($parts as $part) {
                // reassemble and check each tier
                $ability .= $ability ? '.'.$part : $part;

                if (app('auth')->guard($guard)->user()->can($ability)) {
                    //exit on first match
                    return $next($request);
                }
            }
        }

        // if no requested permission tier is matched, deny
        throw UnauthorizedException::forPermissions($permissions);
    }
}
