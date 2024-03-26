<?php

if (! function_exists('getModelForGuard')) {
    function getModelForGuard(string $guard): ?string
    {
        return collect(config('auth.guards'))
            ->map(fn ($guard) => isset($guard['provider']) ? config("auth.providers.{$guard['provider']}.model") : null)
            ->get($guard);
    }
}

if (! function_exists('setPermissionsTeamId')) {
    function setPermissionsTeamId(int|string|\Illuminate\Database\Eloquent\Model|null $id, ?\Spatie\Permission\PermissionRegistrar $permissionRegistrar = null): void
    {
        $permissionRegistrar ??= app(\Spatie\Permission\PermissionRegistrar::class);
        $permissionRegistrar->setPermissionsTeamId($id);
    }
}

if (! function_exists('getPermissionsTeamId')) {
    function getPermissionsTeamId(?\Spatie\Permission\PermissionRegistrar $permissionRegistrar = null): int|string|null
    {
        $permissionRegistrar ??= app(\Spatie\Permission\PermissionRegistrar::class);
        return $permissionRegistrar->getPermissionsTeamId();
    }
}
