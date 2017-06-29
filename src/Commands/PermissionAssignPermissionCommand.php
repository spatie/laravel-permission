<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionAssignPermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:assign:permission 
    {user : User\' Id}
    {--P|permissions= : Permission(s) to assign to user (permission_name1:permission_name2, etc)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign permission(s) to user.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = \App\User::find($this->argument('user'));

        $ra = explode(':', $this->option('permissions'));

        foreach ($ra as $r) {
            $s = explode(':', $r);

            $user->assignPerssion($s[0]);
            $this->info('Assigned user '.$user->name.' permission '.$s[0]);
        }
    }
}
