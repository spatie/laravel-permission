<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionDefaultsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:defaults 
    {--yes : Use affirmative response when confirmation required} 
    {--migrate : Whether to refresh the database}
    {--print : Whether to print the roles/permission map} 
    {--admin : Whether to create the admin role}
    {--s|seed : Whether to run db:seed after refreshing the database}';

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
        if($this->option('migrate') != null) {
            if($this->option('yes') != null || $this->confirm('Are you sure you want to refresh the database?')) {
                $this->warn('Running migrate:refresh to reset the database...');
                $this->call('migrate:refresh');
                $this->warn('Database cleared');

                if($this->option('seed') != null) {
                    if($this->option('yes') != null || $this->confirm('Are you sure you want to seed the database?')) {
                        $this->warn('Running db:seed to seed the database...');
                        $this->call('db:seed');
                        $this->warn('Seeding complete');
                    }
                }
            }
        }

        $this->info('Creating permissions...');

        $roles = \Config::get('permission.roles_permissions');
        
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

        if($this->option('admin') != null) {
            $role = Role::firstOrCreate(['name' => 'admin']);
            $this->info('Added "admin" role');
            $role->syncPermissions(Permission::all());
            $this->info('Adding all permissions to "admin" role');
        }

        if($this->option('print') != null) {
            $this->info('This is the current permissions map:');
            $this->info(print_r($roles, true));
        }
    }
}
