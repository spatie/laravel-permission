<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreatePermissionsMap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:defaults {--m|migrate : Whether to refresh the database} {--p|print : Whether to print the roles/permission map';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates roles/permissions for spatie/laravel-permission.';

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
        if($this->option('migrate')) 
        {
            $this->call('migrate:refresh');
            $this->warn('Database cleared.');
        }

        $this->info('Creating permissions...');

        $roles = \Config::get('permission.roles_permissions');
        
        $this->info('Here is the current permissions map:');
        
        foreach($roles as $role => $permissions)
        {
            $r = Role::firstOrCreate(['name' => $role]);
            $this->info('Adding role: '.$role);

            foreach($permissions as $permission)
            {
                Permission::firstOrCreate(['name' => $permission]);
                $this->info('Adding permission: '.$permission);

                if(!$r->hasPermissionTo($permission))
                {
                    $r->givePermissionTo($permission);
                    $this->info('Adding permission '.$permission.' to role '.$role);
                }
            }
        }

        $role = Role::firstOrCreate(['name' => 'admin']);
        $this->info('Added "admin" role');
        $role->syncPermissions(Permission::all());
        $this->info('Adding all permissions to "admin" role');

        if($this->option('print')) {
            $this->info(print_r($roles, true));
        }
    }
}
