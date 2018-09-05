<?php

namespace Spatie\Permission\Test;

use Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CommandTest extends TestCase
{
    /** @test */
    public function it_can_create_a_role()
    {
        Artisan::call('permission:create-role', ['name' => 'new-role']);

        $this->assertCount(1, Role::where('name', 'new-role')->get());
    }

    /** @test */
    public function it_can_create_a_role_with_a_specific_guard()
    {
        Artisan::call('permission:create-role', [
            'name' => 'new-role',
            'guard' => 'api',
        ]);

        $this->assertCount(1, Role::where('name', 'new-role')
            ->where('guard_name', 'api')
            ->get());
    }

    /** @test */
    public function it_can_create_a_permission()
    {
        Artisan::call('permission:create-permission', ['name' => 'new-permission']);

        $this->assertCount(1, Permission::where('name', 'new-permission')->get());
    }

    /** @test */
    public function it_can_create_a_permission_with_a_specific_guard()
    {
        Artisan::call('permission:create-permission', [
            'name' => 'new-permission',
            'guard' => 'api',
        ]);

        $this->assertCount(1, Permission::where('name', 'new-permission')
            ->where('guard_name', 'api')
            ->get());
    }

    /** @test */
    public function it_can_give_a_role_to_a_specific_user()
    {
        Artisan::call('permission:assign-user', [
            'user' => $this->testUser->id,
            'name' => 'Moderator',
        ]);

        $this->assertTrue($this->testUser->hasRole('Moderator'));
    }

    /** @test */
    public function it_can_give_a_permission_to_a_specific_user()
    {
        Artisan::call('permission:give-user', [
            'user' => $this->testUser->id,
            'name' => 'edit articles',
        ]);

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
    }
}
