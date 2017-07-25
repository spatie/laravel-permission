<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateRole extends Command
{
    protected $signature = 'permission:create-role
        {name : The name of the role}
        {guard? : Optional guard, default web}';

    protected $description = 'Create a role';

    public function handle()
    {
        $permission = Role::create([
            'name' => $this->argument('name'),
            'guard_name' => $this->argument('guard'),
        ]);

        $this->info("Role `{$permission->name}` created");
    }
}
