<?php

namespace Spatie\Permission\Middlewares;

use Closure;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        if (auth()->guest()) {
            abort(403);
        }

        if (is_array($permission)) {
            if (strpos($permission, '|')) {
               $permission = explode('|', $permission);
            }
            if (strpos($permission, ',')) {
               $permission = explode(',', $permission);
            }
        };

        if (! auth()->user()->hasAnyPermission($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
