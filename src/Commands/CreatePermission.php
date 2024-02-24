<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;

class CreatePermission extends Command
{
    protected $signature = 'permission:create-permission
        {name : The name of the permission}
        {guard? : The name of the guard}
        {--p|permission-registrar=}';

    protected $description = 'Create a permission';

    public function handle()
    {
        /** @var PermissionRegistrar $permissionRegistrar */
        $permissionRegistrar = app($this->option('permission-registrar') ?? PermissionRegistrar::class);

        $permissionClass = $permissionRegistrar->getPermissionClass();

        $permission = $permissionClass::findOrCreate($this->argument('name'), $this->argument('guard'));

        $this->info("Permission `{$permission->name}` ".($permission->wasRecentlyCreated ? 'created' : 'already exists'));
    }
}
