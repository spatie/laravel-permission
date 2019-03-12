<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Table extends Command
{
    protected $signature = 'permission:table
            {guard? : The name of the guard}';

    protected $description = 'Show a table of roles and permissions per guard';

    public function handle()
    {
        $guard = $this->argument('guard');

        if ($guard) {
            $guards = Collection::make([$guard]);
        } else {
            $guards = Permission::pluck('guard_name')->merge(Role::pluck('guard_name'))->unique();
        }

        foreach ($guards as $guard) {
            $this->info("Guard: $guard");

            $permissions = Permission::whereGuardName($guard)->orderBy('name')->pluck('name');

            $roles = Role::whereGuardName($guard)->orderBy('name')->get()->mapWithKeys(function (Role $role) {
                return [$role->name => $role->permissions->pluck('name')];
            });

            $header = $roles->keys()->prepend('');

            $body = $permissions->map(function ($permission) use ($roles) {
                return $roles->map(function (Collection $role_permissions) use ($permission) {
                    return $role_permissions->contains($permission) ? ' ✔' : ' ·';
                })->prepend($permission);
            });

            $this->table($header->toArray(), $body->toArray());
        }
    }
}
