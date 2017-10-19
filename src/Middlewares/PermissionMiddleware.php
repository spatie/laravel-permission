<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HandleUnauthorized;

class PermissionMiddleware
{
    use HandleUnauthorized;

    public function handle($request, Closure $next, $permission)
    {

        if (Auth::guest()) {
            $this->handleUnauthorized();
        }

        $permissions = is_array($permission)
            ? $permission
            : explode('|', $permission);

        foreach ($permissions as $permission) {
            if (Auth::user()->can($permission)) {
                return $next($request);
            }
        }

        $this->handleUnauthorized();
    }
}
