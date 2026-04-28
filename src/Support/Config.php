<?php

namespace Spatie\Permission\Support;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Exceptions\TeamModelNotConfigured;
use Spatie\Permission\Exceptions\TeamsNotEnabled;
use Spatie\Permission\PermissionRegistrar;

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

    public static function morphKey(): string
    {
        return config('permission.column_names.model_morph_key');
    }

    public static function teamForeignKey(): string
    {
        return app(PermissionRegistrar::class)->teamsKey;
    }
}
