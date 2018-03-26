<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Role as RoleContract;

class CreateRole extends Command
{
    protected $signature = 'permission:create-role
        {name : The name of the role}';

    protected $description = 'Create a role';

    public function handle()
    {
        $roleClass = app(RoleContract::class);

        $role = $roleClass::create([
            'name' => $this->argument('name'),
        ]);

        $this->info("Role `{$role->name}` created");
    }
}
