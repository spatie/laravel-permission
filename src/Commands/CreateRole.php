<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Role as RoleContract;

class CreateRole extends Command
{
    protected $signature = 'permission:create-role
        {name : The name of the role}
        {flag : The flag of the role}
        {guard? : The name of the guard}';

    protected $description = 'Create a role';

    public function handle()
    {
        $roleClass = app(RoleContract::class);

        $role = $roleClass::create([
            'name' => $this->argument('name'),
            'flag' => $this->argument('flag'),
            'guard_name' => $this->argument('guard'),
        ]);

        $this->info("Role `{$role->name}` created");
    }
}
