<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        $routeNameRedirect = config('permission.unauthorized_route_name_redirect');

        if (Auth::guest()) {
            if (! is_null($routeNameRedirect)) {
                return redirect()
                    ->route($routeNameRedirect);
            }
            abort(403);
        }

        $role = is_array($role)
            ? $role
            : explode('|', $role);

        if (! Auth::user()->hasAnyRole($role)) {
            if (! is_null($routeNameRedirect)) {
                return redirect()
                    ->route($routeNameRedirect);
            }
            abort(403);
        }

        return $next($request);
    }
}
