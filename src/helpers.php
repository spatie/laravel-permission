<?php

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Guard;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Support\Config;

if (! function_exists('getModelForGuard')) {
    function getModelForGuard(string $guard): ?string
    {
        return Guard::getModelForGuard($guard);
    }
}

if (! function_exists('setPermissionsTeamId')) {
    function setPermissionsTeamId(int|string|Model|null $id): void
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

if (! function_exists('resolveTeam')) {
    function resolveTeam(int|string|Model $team): Model
    {
        $teamModel = Config::teamModel();

        if ($team instanceof Model && ! ($team instanceof $teamModel)) {
            throw new InvalidArgumentException('The team must be an instance of the configured team model.');
        }

        $teamId = $team instanceof Model ? $team->getKey() : $team;
        $resolvedTeam = $teamModel::query()->find($teamId);

        if (! $resolvedTeam) {
            throw new InvalidArgumentException("Team with ID $teamId does not exist.");
        }

        return $team instanceof $teamModel ? $team : $resolvedTeam;
    }
}

if (! function_exists('runOnAnotherTeam')) {
    /**
     * Run a callback within another team's context.
     *
     * Clears loaded roles and permissions before and after the callback so
     * they are resolved for the active team on their next access.
     */
    function runOnAnotherTeam(int|string|Model $team, Model&Authenticatable $user, callable $callback): mixed
    {
        $team = resolveTeam($team);
        $teamId = $team->getKey();

        $currentTeam = getPermissionsTeamId();

        try {
            setPermissionsTeamId($teamId);
            $user->unsetRelation('roles')->unsetRelation('permissions');

            return $callback();
        } finally {
            setPermissionsTeamId($currentTeam);
            $user->unsetRelation('roles')->unsetRelation('permissions');
        }
    }
}
