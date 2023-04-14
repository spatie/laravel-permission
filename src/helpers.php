<?php

if (! function_exists('getModelForGuard')) {
    /**
     * @return string|null
     */
    function getModelForGuard(string $guard)
    {
        return collect(config('auth.guards'))
            ->map(fn ($guard) => isset($guard['provider']) ? config("auth.providers.{$guard['provider']}.model") : null)
            ->get($guard);
    }
}

if (! function_exists('setPermissionsTeamId')) {
    /**
     * @param  int|string|\Illuminate\Database\Eloquent\Model  $id
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
        return app(\Spatie\Permission\PermissionRegistrar::class)->getPermissionsTeamId();
    }
}
