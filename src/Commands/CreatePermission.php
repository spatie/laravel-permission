<?php

namespace Spatie\Permission\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class CreatePermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:create-permission {name : The name of the permission} {guard? : Optional guard, default web}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a permission';

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
        $permission_data = ['name' => $this->argument('name')];

        $guard = $this->argument('guard');
        if ($guard) {
            $permission_data['guard_name'] = $guard;
        }

        $permission = Permission::create($permission_data);
        $this->info('Permission `'.$permission->name.'` created at ID: '.$permission->id);
    }
}
