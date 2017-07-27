<?php
namespace Spatie\Permission\Middleware;

use Closure;
use Auth;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        if (Auth::guest()) {
            return $next($request);
        }

        if ($permission && ! Auth::user()->can($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
