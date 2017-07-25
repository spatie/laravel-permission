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

        $role = Role::create($attributes);

        $this->info("Role `{$role->name}` created ");
    }
}
