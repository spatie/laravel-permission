<?php

namespace Spatie\Permission\Console;

use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class GivePermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:give-permission
        {--user : Give permission(s) to user}
        {--role : Give permission(s) to role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Give permission(s) to user or role';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('user')) {
            return $this->givePermissionToUser();
        } else if ($this->option('role')) {
            return $this->givePermissionToRole();
        }

        $this->askType();
    }

    /**
     * Handle input of type.
     *
     * @return mixed
     */
    protected function askType()
    {
        $type = $this->ask('Choose type (user|role)');

        if (! in_array($type, ['user', 'role'])) {
            return $this->error('Type not support');
        }

        if ($type === 'user') {
            $this->givePermissionToUser();
        } else {
            $this->givePermissionToRole();
        }
    }

    /**
     * Handle give permissions to role.
     *
     * @return mixed
     */
    protected function givePermissionToRole()
    {
        $role = $this->ask('Input role name');
        $this->line('<comment>If multiple permissions, separate with comma!</comment>');
        $permissions = $this->ask('Give permission(s)');

        try {
            $role = app(Role::class)->findByName($role);
        } catch (RoleDoesNotExist $e) {
            return $this->error("Role doesn't exist");
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

        $this->givePermissionTo($role, $permissions);
    }

    /**
     * Handle give permissions to user.
     *
     * @return mixed
     */
    protected function givePermissionToUser()
    {
        $email = $this->ask('Input user email');
        $this->line('<comment>If multiple permissions, separate with comma!</comment>');
        $permissions = $this->ask('Give permission(s)');

        $userModel = config('auth.model') ?: config('auth.providers.users.model');

        if (! $user = $userModel::where('email', $email)->first()) {
            return $this->error("User doesn't exist");
        }

        $this->givePermissionTo($user, $permissions);
    }

    /**
     * Give permissions to user or role.
     *
     * @param $instance User or \Spatie\Permission\Contracts\Role instance
     * @param $permissions string
     *
     * @return mixed
     */
    protected function givePermissionTo($instance, $permissions)
    {
        try {
            $instance->givePermissionTo($this->toArray($permissions));
        } catch (PermissionDoesNotExist $e) {
            return $this->error("Permission(s) doesn't exist");
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

        $this->info('Success!');
    }

    /**
     * Parse permissions to array.
     *
     * @param  $permissions string
     *
     * @return array
     */
    protected function toArray($permissions)
    {
        return collect(explode(',', $permissions))
            ->flatten()
            ->map(function ($permission) {
                return trim(strtolower($permission));
            })
            ->all();
    }
}
