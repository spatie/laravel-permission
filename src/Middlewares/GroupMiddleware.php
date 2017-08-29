<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;

class GroupMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        if (Auth::guest()) {
            abort(403);
        }

        $role = is_array($role)
            ? $role
            : explode('|', $role);

        if (! Auth::user()->hasAnyGroup($role)) {
            abort(403);
        }

        return $next($request);
    }
}
