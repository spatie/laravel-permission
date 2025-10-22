<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Role as RoleContract;

class AssignRole extends Command
{
    protected $signature = 'permission:assign-role
        {name : The name of the role}
        {userId : The ID of the user to assign the role to}
        {guard? : The name of the guard}
        {userModelNamespace=App\Models\User : The fully qualified class name of the user model}';

    protected $description = 'Assign a role to a user. (Note: does not support Teams.)';

    public function handle()
    {
        $roleName = $this->argument('name');
        $userId = $this->argument('userId');
        $guardName = $this->argument('guard');
        $userModelClass = $this->argument('userModelNamespace');

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

        /** @var \Spatie\Permission\Contracts\Role $roleClass */
        $roleClass = app(RoleContract::class);

        $role = $roleClass::findOrCreate($roleName, $guardName);

        $user->assignRole($role);

        $this->info("Role `{$role->name}` assigned to user ID {$userId} successfully.");

        return Command::SUCCESS;
    }
}
