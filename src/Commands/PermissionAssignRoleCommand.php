<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionAssignRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:assign:role 
    {user : User\' Id}
    {--r|roles= : Role(s) to assign to user (role_name1:role_name2, etc)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign role(s) to user';

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

        $ra = explode(':', $this->option('roles'));

        foreach($ra as $r) {
            $s = explode(':', $r);
            
            $user->assignRole($s[0]);
            $this->info('Assigned user '.$user->name.' to role '.$s[0]);
        }
    }
}
