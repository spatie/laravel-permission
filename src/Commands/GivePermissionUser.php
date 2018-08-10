<?php

namespace Spatie\Permission\Commands;

use App\User;
use Illuminate\Console\Command;
use Spatie\Permission\Contracts\Permission as PermissionContract;

class GivePermissionUser extends Command
{
    protected $signature = 'permission:give-user
        {user : User ID}
        {name : The name of the permission}
        {guard? : The name of the guard}';

    protected $description = 'Give user a permission';

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
        $permissionClass = app(PermissionContract::class);

        $user       = $this->user->find($this->argument('user'));
        $permission = $permissionClass::findByName($this->argument('name'), $this->argument('guard'));

        $user->givePermissionTo($permission->name);
        $this->info("Permission `{$permission->name}` given to `{$user->name}` (ID:{$user->name})");
    }
}
