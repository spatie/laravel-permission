<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\Console\Helper\TableCell;

class Show extends Command
{
    protected $signature = 'permission:show
        {guard? : The name of the guard}
        {style? : The display style (default|borderless|compact|box)}
        {--p|permission-registrar=}';

    protected $description = 'Show a table of roles and permissions per guard';

    public function handle()
    {
        /** @var PermissionRegistrar $permissionRegistrar */
        $permissionRegistrar = app($this->option('permission-registrar') ?? PermissionRegistrar::class);

        $permissionClass = $permissionRegistrar->getPermissionClass();
        $roleClass = $permissionRegistrar->getRoleClass();
        $teamsEnabled = $permissionRegistrar->teams;
        $team_key = $permissionRegistrar->teamsKey;

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
                ->when($teamsEnabled, fn ($q) => $q->orderBy($team_key))
                ->orderBy('name')->get()->mapWithKeys(fn ($role) => [
                    $role->name.'_'.($teamsEnabled ? ($role->$team_key ?: '') : '') => [
                        'permissions' => $role->permissions->pluck('id'),
                        $team_key => $teamsEnabled ? $role->$team_key : null,
                    ],
                ]);

            $permissions = $permissionClass::whereGuardName($guard)->orderBy('name')->pluck('name', 'id');

            $body = $permissions->map(fn ($permission, $id) => $roles->map(
                fn (array $role_data) => $role_data['permissions']->contains($id) ? ' ✔' : ' ·'
            )->prepend($permission)
            );

            if ($teamsEnabled) {
                $teams = $roles->groupBy($team_key)->values()->map(
                    fn ($group, $id) => new TableCell('Team ID: '.($id ?: 'NULL'), ['colspan' => $group->count()])
                );
            }

            $this->table(
                array_merge(
                    isset($teams) ? $teams->prepend(new TableCell(''))->toArray() : [],
                    $roles->keys()->map(function ($val) {
                        $name = explode('_', $val);
                        array_pop($name);

                        return implode('_', $name);
                    })
                        ->prepend(new TableCell(''))->toArray(),
                ),
                $body->toArray(),
                $style
            );
        }
    }
}
