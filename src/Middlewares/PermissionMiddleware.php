<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        if (Auth::guest()) {
            abort(403);
        }

        $permission = is_array($permission)
            ? $permission
            : explode('|', $permission);

        foreach ($permission as $p) {
            if (Auth::user()->can($p)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
