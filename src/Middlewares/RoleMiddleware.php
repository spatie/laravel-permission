<?php

namespace Spatie\Permission\Middlewares;

use Closure;

class RoleMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        if (auth()->guest()) {
            abort(403);
        }

        if (is_array($role)) {
            if (strpos($role, '|')) {
               $role = explode('|', $role);
            }
            if (strpos($role, ',')) {
               $role = explode(',', $role);
            }
        };

        if (! auth()->user()->hasAnyRole($role)) {
            abort(403);
        }

        return $next($request);
    }
}
