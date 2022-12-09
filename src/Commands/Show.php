<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;
use Symfony\Component\Console\Helper\TableCell;

class Show extends Command
{
    protected $signature = 'permission:show
            {guard? : The name of the guard}
            {style? : The display style (default|borderless|compact|box)}';

    protected $description = 'Show a table of roles and permissions per guard';

    public function handle()
    {
        $permissionClass = app(PermissionContract::class);
        $roleClass = app(RoleContract::class);
        $team_key = config('permission.column_names.team_foreign_key');

        $style = $this->argument('style') ?? 'default';
        $guard = $this->argument('guard');

        if ($guard) {
            $guards = Collection::make([$guard]);
        } else {
            $guards = $permissionClass::pluck('guard_name')->merge($roleClass::pluck('guard_name'))->unique();
        }

        foreach ($guards as $guard) {
            $this->info("Guard: $guard");

            $roles = $roleClass::whereGuardName($guard)
                ->with('permissions')
                ->when(config('permission.teams'), function ($q) use ($team_key) {
                    $q->orderBy($team_key);
                })
                ->orderBy('name')->get()->mapWithKeys(function ($role) use ($team_key) {
                    return [$role->name.'_'.($role->$team_key ?: '') => ['permissions' => $role->permissions->pluck('id'), $team_key => $role->$team_key]];
                });

            $permissions = $permissionClass::whereGuardName($guard)->orderBy('name')->pluck('name', 'id');

            $body = $permissions->map(function ($permission, $id) use ($roles) {
                return $roles->map(function (array $role_data) use ($id) {
                    return $role_data['permissions']->contains($id) ? ' ✔' : ' ·';
                })->prepend($permission);
            });

            if (config('permission.teams')) {
                $teams = $roles->groupBy($team_key)->values()->map(function ($group, $id) {
                    return new TableCell('Team ID: '.($id ?: 'NULL'), ['colspan' => $group->count()]);
                });
            }

            $this->table(
                array_merge([
                    config('permission.teams') ? $teams->prepend('')->toArray() : [],
                    $roles->keys()->map(function ($val) {
                        $name = explode('_', $val);

                        return $name[0];
                    })
                    ->prepend('')->toArray(),
                ]),
                $body->toArray(),
                $style
            );
        }
    }
}
