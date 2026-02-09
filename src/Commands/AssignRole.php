<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\PermissionRegistrar;

class AssignRole extends Command
{
    protected $signature = 'permission:assign-role
        {name : The name of the role}
        {userId : The ID of the user to assign the role to}
        {guard? : The name of the guard}
        {userModelNamespace=App\Models\User : The fully qualified class name of the user model}
        {--team-id=}';

    protected $description = 'Assign a role to a user';

    public function handle(PermissionRegistrar $permissionRegistrar)
    {
        $roleName = $this->argument('name');
        $userId = $this->argument('userId');
        $guardName = $this->argument('guard');
        $userModelClass = $this->argument('userModelNamespace');

        if (! $permissionRegistrar->teams && $this->option('team-id')) {
            $this->warn('Teams feature disabled, argument --team-id has no effect. Either enable it in permissions config file or remove --team-id parameter');

            return;
        }

        // Validate that the model class exists and is instantiable
        if (! class_exists($userModelClass)) {
            $this->error("User model class [{$userModelClass}] does not exist.");

            return Command::FAILURE;
        }

        $user = (new $userModelClass)::find($userId);

        if (! $user) {
            $this->error("User with ID {$userId} not found.");

            return Command::FAILURE;
        }

        $teamIdAux = getPermissionsTeamId();
        setPermissionsTeamId($this->option('team-id') ?: null);

        /** @var \Spatie\Permission\Contracts\Role $roleClass */
        $roleClass = app(RoleContract::class);

        $role = $roleClass::findOrCreate($roleName, $guardName);

        $user->assignRole($role);

        setPermissionsTeamId($teamIdAux);

        $this->info("Role `{$role->name}` assigned to user ID {$userId} successfully.");

        return Command::SUCCESS;
    }
}
