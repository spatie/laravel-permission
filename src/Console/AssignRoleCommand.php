<?php

namespace Spatie\Permission\Console;

use Illuminate\Console\Command;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class AssignRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:assign-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign role(s) to user';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $email = $this->ask('Input user email');
        $this->line('<comment>If multiple roles, separate with comma!</comment>');
        $roles = $this->ask('Assign role(s)');

        $userModel = config('auth.model') ?: config('auth.providers.users.model');

        if (! $user = $userModel::where('email', $email)->first()) {
            return $this->error("User doesn't exist");
        }

        $this->assignRoleTo($user, $roles);
    }

    /**
     * Handle assign role to user.
     *
     * @param  $user User
     * @param  $roles string
     *
     * @return mixed
     */
    protected function assignRoleTo($user, $roles)
    {
        try {
            $user->assignRole($this->toArray($roles));
        } catch (RoleDoesNotExist $e) {
            return $this->error("Role(s) doesn't exist");
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

        $this->info('Success!');
    }

    /**
     * Parse roles to array.
     *
     * @param  $roles string
     *
     * @return array
     */
    protected function toArray($roles)
    {
        return collect(explode(',', $roles))
            ->flatten()
            ->map(function ($role) {
                return trim(strtolower($role));
            })
            ->all();
    }
}
