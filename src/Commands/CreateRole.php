<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:create-role {name : The name of the role} {guard? : Optional guard, default web}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a role';

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
        $role_data = ['name' => $this->argument('name')];

        $guard = $this->argument('guard');
        if ($guard) {
            $role_data['guard_name'] = $guard;
        }

        $role = Role::create($role_data);
        $this->info('Role `'.$role->name.'` created at ID: '.$role->id);
    }
}
