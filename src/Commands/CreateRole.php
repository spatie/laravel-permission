<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\PermissionRegistrar;

class CreateRole extends Command
{
    protected $signature = 'permission:create-role
        {name : The name of the role}
        {guard? : The name of the guard}
        {permissions? : A list of permissions to assign to the role, separated by | }
        {--team-id=}';

    protected $description = 'Create a role';

    public function handle()
    {
        $roleClass = app(RoleContract::class);

        $teamIdAux = getPermissionsTeamId();
        setPermissionsTeamId($this->option('team-id') ?: null);

        if (! PermissionRegistrar::$teams && $this->option('team-id')) {
            $this->warn("Teams feature disabled, argument --team-id has no effect. Either enable it in permissions config file or remove --team-id parameter");

            return;
        }

        $role = $roleClass::findOrCreate($this->argument('name'), $this->argument('guard'));
        setPermissionsTeamId($teamIdAux);

        $teams_key = PermissionRegistrar::$teamsKey;
        if (PermissionRegistrar::$teams && $this->option('team-id') && is_null($role->$teams_key)) {
            $this->warn("Role `{$role->name}` already exists on the global team; argument --team-id has no effect");
        }

        $role->givePermissionTo($this->makePermissions($this->argument('permissions')));

        $this->info("Role `{$role->name}` ".($role->wasRecentlyCreated ? 'created' : 'updated'));
    }

    /**
     * @param array|null|string $string
     */
    protected function makePermissions($string = null)
    {
        if (empty($string)) {
            return;
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
