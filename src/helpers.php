<?php

if (! function_exists('getModelForGuard')) {
    /**
     * @param string $guard
     *
     * @return string|null
     */
    function getModelForGuard(string $guard)
    {
        return collect(config('auth.guards'))
            ->map(function ($guard) {
                if (! isset($guard['provider'])) {
                    return;
                }

                return config("auth.providers.{$guard['provider']}.model");
            })->get($guard);
    }
}

if (! function_exists('currentAuthGuard')) {
    /**
     *
     * @return string|null
     */
    function currentAuthGuard()
    {
        if(! config('permission.enable_dynamic_auth_guard_checks')) {
            return null;
        }

        foreach (array_keys(config('auth.guards')) as $guard) {

            if (auth()->guard($guard)->check()) return $guard;
        }
        return null;
    }
}
