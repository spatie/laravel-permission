<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class CreatePermission extends Command
{
    protected $signature = 'permission:create-permission 
                {name : The name of the permission} 
                {guard? : Optional guard, default web}';

    protected $description = 'Create a permission';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $attributes = ['name' => $this->argument('name')];

        $guard = $this->argument('guard');

        if ($guard) {
            $attributes['guard_name'] = $guard;
        }

        $permission = Permission::create($attributes);

        $this->info("Permission `{$permission->name}` created");
    }
}
