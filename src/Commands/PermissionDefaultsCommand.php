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
    {--a|admin : Create the admin role}
    {--m|migrate : Refresh the database (migrate:refresh)}
    {--p|print : Print the roles/permission map} 
    {--P|process : Process the roles/permission map and add to database}
    {--r|role=* : Assign user roles (user_id:role_name)}
    {--s|seed : Run db:seed after refreshing the database}
    {--y|yes : Use affirmative response when confirmation required} ';

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
        if ($this->option('migrate') != null) {
            if ($this->option('yes') != null || $this->confirm('Are you sure you want to refresh the database?')) {
                $this->warn('Running migrate:refresh to reset the database...');
                $this->call('migrate:refresh');
                $this->warn('Database cleared');

                if ($this->option('seed') != null) {
                    if ($this->option('yes') != null || $this->confirm('Are you sure you want to seed the database?')) {
                        $this->warn('Running db:seed to seed the database...');
                        $this->call('db:seed');
                        $this->warn('Seeding complete');
                    }
                }
            }
        }

        if ($this->option('process') != null) {
            $this->info('Creating permissions...');

            $roles = \Config::get('permission.roles_permissions');

            foreach ($roles as $role => $permissions)
            {
                $r = Role::firstOrCreate(['name' => $role]);
                $this->info('Adding role: '.$role);

                foreach ($permissions as $permission)
                {
                    Permission::firstOrCreate(['name' => $permission]);
                    $this->info('Adding permission: '.$permission);

                    if (!$r->hasPermissionTo($permission))
                    {
                        $r->givePermissionTo($permission);
                        $this->info('Adding permission '.$permission.' to role '.$role);
                    }
                }
            }

            if ($this->option('admin') != null) {
                $role = Role::firstOrCreate(['name' => 'admin']);
                $this->info('Added "admin" role');
                $role->syncPermissions(Permission::all());
                $this->info('Adding all permissions to "admin" role');
            }
        }

        $ra = $this->option('role');

        if ($ra != null) {
            $this->info('Assigning roles based on command-line input...');
            foreach ($ra as $r) {
                $s = explode(':', $r, 2);
                $this->info('Assign roles for '.$s[0]);
                $user = \App\User::find($s[0]);
                $user->assignRole($s[1]);
                $this->info('Assigned user '.$user->name.' to role '.$s[1]);
            }
        }

        if ($this->option('print') != null) {
            $this->info('This is the current permissions map:');
            $this->info(print_r($roles, true));
        }
    }
}
