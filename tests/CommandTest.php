<?php

namespace Spatie\Permission\Test;

use Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CommandTest extends TestCase
{
    /** @test */
    public function evaluate_command_create_role()
    {
        Artisan::call('permission:create-role', ['name' => 'rolebycommand']);
        $resultAsText = Artisan::output();
        $role = Role::where('name', 'rolebycommand')->first();
        $this->assertStringStartsWith('Role `rolebycommand` created at ID: '.$role->id."\n", $resultAsText);
    }

    /** @test */
    public function evaluate_command_create_permission()
    {
        Artisan::call('permission:create-permission', ['name' => 'permissionbycommand']);
        $resultAsText = Artisan::output();
        $permission = Permission::where('name', 'permissionbycommand')->first();
        $this->assertStringStartsWith('Permission `permissionbycommand` created at ID: '.$permission->id."\n", $resultAsText);
    }
}
