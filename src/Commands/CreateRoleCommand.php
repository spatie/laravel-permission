<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\PermissionRegistrar;

class CreateRoleCommand extends Command
{
    protected $signature = 'permission:create-role
        {name : The name of the role}
        {guard? : The name of the guard}
        {permissions? : A list of permissions to assign to the role, separated by | }
        {--team-id=}';

    protected $description = 'Create a role';

    public function handle(PermissionRegistrar $permissionRegistrar): int
    {
        $roleClass = app(RoleContract::class);

        $teamIdAux = getPermissionsTeamId();
        setPermissionsTeamId($this->option('team-id') ?: null);

        if (! $permissionRegistrar->teams && $this->option('team-id')) {
            $this->warn('Teams feature disabled, argument --team-id has no effect. Either enable it in permissions config file or remove --team-id parameter');

            return self::SUCCESS;
        }

        $role = $roleClass::findOrCreate($this->argument('name'), $this->argument('guard'));
        setPermissionsTeamId($teamIdAux);

        $teams_key = $permissionRegistrar->teamsKey;
        if ($permissionRegistrar->teams && $this->option('team-id') && is_null($role->$teams_key)) {
            $this->warn("Role `{$role->name}` already exists on the global team; argument --team-id has no effect");
        }

        $role->givePermissionTo($this->makePermissions($this->argument('permissions')));

        $this->info("Role `{$role->name}` ".($role->wasRecentlyCreated ? 'created' : 'updated'));

        return self::SUCCESS;
    }

    protected function makePermissions(?string $string = null): ?Collection
    {
        if (empty($string)) {
            return null;
        }

        $permissionClass = app(PermissionContract::class);

        $permissions = explode('|', $string);

        $models = [];

        foreach ($permissions as $permission) {
            $models[] = $permissionClass::findOrCreate(trim($permission), $this->argument('guard'));
        }

        return collect($models);
    }
}
