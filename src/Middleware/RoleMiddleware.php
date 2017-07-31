<?php

namespace Spatie\Permission\Middleware;

use Auth;
use Closure;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        if (Auth::guest()) {
            abort(403);
        }

        $role = (is_array($role)
        ? $role
        : explode('|', $role));

        if (! Auth::user()->hasAnyRole($role)) {
            abort(403);
        }

        return $next($request);
    }
}
