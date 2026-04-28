<?php

namespace Spatie\Permission\Support;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\Wildcard;
use Spatie\Permission\Exceptions\TeamModelNotConfigured;
use Spatie\Permission\Exceptions\TeamsNotEnabled;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\WildcardPermission;

class Config
{
    public static function teamsEnabled(): bool
    {
        return app(PermissionRegistrar::class)->teams;
    }

    public static function ensureTeamsEnabled(): void
    {
        if (! self::teamsEnabled()) {
            throw TeamsNotEnabled::create();
        }
    }

    /**
     * @return class-string<Model>
     */
    public static function teamModel(): string
    {
        self::ensureTeamsEnabled();

        $teamModel = app(PermissionRegistrar::class)->getTeamClass();

        if (! $teamModel) {
            throw TeamModelNotConfigured::create();
        }

        return $teamModel;
    }

    public static function modelHasRolesTable(): string
    {
        return config('permission.table_names.model_has_roles');
    }

    public static function modelHasPermissionsTable(): string
    {
        return config('permission.table_names.model_has_permissions');
    }

    public static function roleHasPermissionsTable(): string
    {
        return config('permission.table_names.role_has_permissions');
    }

    public static function rolesTable(): string
    {
        return config('permission.table_names.roles');
    }

    public static function permissionsTable(): string
    {
        return config('permission.table_names.permissions');
    }

    public static function morphKey(): string
    {
        return config('permission.column_names.model_morph_key');
    }

    public static function teamForeignKey(): string
    {
        return app(PermissionRegistrar::class)->teamsKey;
    }

    /**
     * @return class-string<Model>
     */
    public static function roleModel(): string
    {
        return app(PermissionRegistrar::class)->getRoleClass();
    }

    /**
     * @return class-string<Model>
     */
    public static function permissionModel(): string
    {
        return app(PermissionRegistrar::class)->getPermissionClass();
    }

    public static function eventsEnabled(): bool
    {
        return (bool) config('permission.events_enabled');
    }

    public static function usePassportClientCredentials(): bool
    {
        return (bool) config('permission.use_passport_client_credentials');
    }

    public static function displayRoleInException(): bool
    {
        return (bool) config('permission.display_role_in_exception');
    }

    public static function displayPermissionInException(): bool
    {
        return (bool) config('permission.display_permission_in_exception');
    }

    public static function wildcardPermissionsEnabled(): bool
    {
        return (bool) config('permission.enable_wildcard_permission');
    }

    /**
     * @return class-string<Wildcard>
     */
    public static function wildcardPermissionClass(): string
    {
        return config('permission.wildcard_permission', WildcardPermission::class);
    }
}
