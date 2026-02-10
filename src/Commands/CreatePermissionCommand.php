<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Permission as PermissionContract;

class CreatePermissionCommand extends Command
{
    protected $signature = 'permission:create-permission
                {name : The name of the permission}
                {guard? : The name of the guard}';

    protected $description = 'Create a permission';

    public function handle(): int
    {
        $permissionClass = app(PermissionContract::class);

        $permission = $permissionClass::findOrCreate($this->argument('name'), $this->argument('guard'));

        $this->info("Permission `{$permission->name}` ".($permission->wasRecentlyCreated ? 'created' : 'already exists'));

        return self::SUCCESS;
    }
}
