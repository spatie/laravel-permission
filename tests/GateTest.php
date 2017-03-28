<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class GateTest extends TestCase
{
    /** @test */
    public function it_can_determine_if_a_user_has_a_permission_when_using_roles()
    {
        $this->testUserRole->givePermissionTo($this->testUserPermission);

        $this->testUser->assignRole($this->testUserRole);

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission));

        $this->assertTrue($this->reloadPermissions());

        $this->assertTrue($this->testUser->can('edit-articles'));

        $this->assertFalse($this->testUser->can('non-existing-permission'));

        $this->assertFalse($this->testUser->can('admin-permission'));
    }
    /** @test */
    public function it_can_determine_if_a_user_with_a_different_guard_has_a_permission_when_using_roles()
    {
        $this->testAdminRole->givePermissionTo($this->testAdminPermission);

        $this->testAdmin->assignRole($this->testAdminRole);

        $this->assertTrue($this->testAdmin->hasPermissionTo($this->testAdminPermission));

        $this->assertTrue($this->reloadPermissions());

        $this->assertTrue($this->testAdmin->can('admin-permission'));

        $this->assertFalse($this->testAdmin->can('non-existing-permission'));

        $this->assertFalse($this->testAdmin->can('edit-articles'));
    }

    /** @test */
    public function it_can_determine_if_a_user_has_a_permission_when_direct_permissions()
    {
        $this->testUser->givePermissionTo($this->testUserPermission);

        $this->assertTrue($this->reloadPermissions());

        $this->assertTrue($this->testUser->can('edit-articles'));

        $this->assertFalse($this->testUser->can('non-existing-permission'));

        $this->assertFalse($this->testUser->can('admin-permission'));
    }

    /** @test */
    public function it_will_throw_an_exception_when_using_a_permission_that_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUserRole->givePermissionTo('create-evil-empire');
    }

    /** @test */
    public function it_will_throw_an_exception_when_assign_a_role_that_does_not_exist()
    {
        $this->expectException(RoleDoesNotExist::class);

        $this->testUser->assignRole('evil-emperor');
    }

    /** @test */
    public function it_can_determine_if_a_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->can('edit-articles'));
    }
}
