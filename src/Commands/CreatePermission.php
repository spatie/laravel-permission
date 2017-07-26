<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class CreatePermission extends Command
{
    protected $signature = 'permission:create-permission 
                {name : The name of the permission} 
                {guard? : The name of the guard}';

    protected $description = 'Create a permission';

    public function handle()
    {
        $permissionModel = $this->resolvePermission();

        $permission = $permissionModel::create([
            'name' => $this->argument('name'),
            'guard_name' => $this->argument('guard'),
        ]);

        $this->info("Permission `{$permission->name}` created");
    }

    private function resolvePermission()
    {
        $class = Config::get('permission.models.permission');

        return app($class);
    }
}
