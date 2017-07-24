<?php

namespace Spatie\Permission\Test;

use Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CommandTest extends TestCase
{

    /** @test */
    public function evaluate_command_add_role()
    {
        Artisan::call('permission:add_role', ['name' => 'rolebycommand']);
        $resultAsText = Artisan::output();
        $role=Role::where('name','rolebycommand')->first();
        $this->assertStringStartsWith("Role `rolebycommand` created at ID: ".$role->id."\n",$resultAsText);
    }

    /** @test */
    public function evaluate_command_add_permission()
    {
        Artisan::call('permission:add_permission', ['name' => 'permissionbycommand']);
        $resultAsText = Artisan::output();
        $permission=Permission::where('name','permissionbycommand')->first();
        $this->assertStringStartsWith("Permission `permissionbycommand` created at ID: ".$permission->id."\n",$resultAsText);
    }

}
