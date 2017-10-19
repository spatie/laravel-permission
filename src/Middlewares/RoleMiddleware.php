<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HandleUnauthorized;

class RoleMiddleware
{
    use HandleUnauthorized;

    public function handle($request, Closure $next, $role)
    {

        if (Auth::guest()) {
            $this->responseUnauthorized();
        }

        $role = is_array($role)
            ? $role
            : explode('|', $role);

        if (! Auth::user()->hasAnyRole($role)) {
            $this->responseUnauthorized();
        }

        return $next($request);
    }
}
