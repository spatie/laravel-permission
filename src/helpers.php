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

if (! function_exists('setPermissionsTeamId')) {
    /**
     * @param int|string|\Illuminate\Database\Eloquent\Model $id
     *
     */
    function setPermissionsTeamId($id)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($id);
    }
}

if (! function_exists('getPermissionsTeamId')) {
    /**
     * @return int|string
     */
    function getPermissionsTeamId()
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->getPermissionsTeamId();
    }
}
