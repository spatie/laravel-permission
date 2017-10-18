<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        $routeNameRedirect = config('permission.unauthorized_route_name_redirect');

        if (Auth::guest()) {
            if(! is_null($routeNameRedirect)) {
                return redirect()
                    ->route($routeNameRedirect);
            }
            abort(403);
        }

        $permissions = is_array($permission)
            ? $permission
            : explode('|', $permission);

        foreach ($permissions as $permission) {
            if (Auth::user()->can($permission)) {
                return $next($request);
            }
        }

        if(! is_null($routeNameRedirect)) {
            return redirect()
                ->route($routeNameRedirect);
        }
        abort(403);
    }
}
