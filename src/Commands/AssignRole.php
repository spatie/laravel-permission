<?php

namespace Spatie\Permission\Commands;

use App\User;
use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Role as RoleContract;

class AssignRole extends Command
{
    protected $signature = 'permission:assign
        {user : User ID}
        {name : The name of the role}
        {guard? : The name of the guard}';

    protected $description = 'Assign a role';

    /**
     * User model.
     *
     * @var User $user
     */
    protected $user;

    /**
     * Create a new command instance.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct(User $user)
    {
        parent::__construct();

        $this->user = $user;
    }

    public function handle()
    {
        $roleClass = app(RoleContract::class);

        $user = $this->user->find($this->argument('user'));
        $role = $roleClass::findByName($this->argument('name'), $this->argument('guard'));

        $user->assignRole($role->name);
        $this->info("Role `{$role->name}` assigned to `{$user->name}` (ID:{$user->name}) created");
    }
}
