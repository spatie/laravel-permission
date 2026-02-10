<?php

use Spatie\Permission\Guard;
use Spatie\Permission\PermissionRegistrar;

if (! function_exists('getModelForGuard')) {
    function getModelForGuard(string $guard): ?string
    {
        return Guard::getModelForGuard($guard);
    }

}

if (! function_exists('setPermissionsTeamId')) {
    /**
     * @param  int|string|null|\Illuminate\Database\Eloquent\Model  $id
     */
    function setPermissionsTeamId($id): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId($id);
    }
}

if (! function_exists('getPermissionsTeamId')) {
    function getPermissionsTeamId(): int|string|null
    {
        return app(PermissionRegistrar::class)->getPermissionsTeamId();
    }
}
