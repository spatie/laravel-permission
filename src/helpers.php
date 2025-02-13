<?php

if (! function_exists('getModelForGuard')) {
    function getModelForGuard(string $guard): ?string
    {
        return Spatie\Permission\Guard::getModelForGuard($guard);
    }

}

if (! function_exists('setPermissionsTeamId')) {
    /**
     * @param  int|string|null|\Illuminate\Database\Eloquent\Model  $id
     */
    function setPermissionsTeamId($id)
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($id);
    }
}

if (! function_exists('getPermissionsTeamId')) {
    /**
     * @return int|string|null
     */
    function getPermissionsTeamId()
    {
        return app(\Spatie\Permission\PermissionRegistrar::class)->getPermissionsTeamId();
    }
}
