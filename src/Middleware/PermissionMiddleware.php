<?php

namespace Spatie\Permission\Middleware;

use Auth;
use Closure;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        if (Auth::guest()) {
            abort(403);
        }

        $permission = (is_array($permission)
        ? $permission
        : explode('|', $permission));

        if (! Auth::user()->hasAnyPermission(...$permission)) {
            abort(403);
        }

        return $next($request);
    }
}
