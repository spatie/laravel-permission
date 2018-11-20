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
     * checks this permissions in this order and exits successfully if found
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
    public function handle($request, Closure $next, $permission)
    {
        //guests are not allowed
        if (app('auth')->guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        //make array
        $permissions = is_array($permission) ? $permission : explode('|', $permission);

        foreach ($permissions as $permission) {

            //split on the '.'
            $parts = explode('.', $permission);
            $ability = '';

            foreach ($parts as $part) {

                //reassemble
                $ability .= $ability ? '.'.$part : $part;

                if (app('auth')->user()->can($ability)) {
                    //exit on first match
                    return $next($request);
                }
            }
        }

        //if no permission is matched, deny
        throw UnauthorizedException::forPermissions($permissions);
    }
}
