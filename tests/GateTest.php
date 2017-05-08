<?php

namespace Spatie\Permission\Test;

class GateTest extends TestCase
{
    /** @test */
    public function it_can_determine_if_a_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->can('edit-articles'));
    }

    /** @test */
    public function it_can_determine_if_a_user_does_not_have_a_scoped_permission()
    {
        $this->assertFalse($this->testUser->can('edit-articles', $this->testRestrictable1));
    }

    /** @test */
    public function it_can_determine_if_a_user_has_a_direct_permission()
    {
        $this->testUser->givePermissionTo($this->testUserPermission);

        $this->assertTrue($this->reloadPermissions());

        $this->assertTrue($this->testUser->can('edit-articles'));

        $this->assertFalse($this->testUser->can('non-existing-permission'));

        $this->assertFalse($this->testUser->can('admin-permission'));
    }

    /** @test */
    public function it_can_determine_if_a_user_has_a_direct_scoped_permission()
    {
        $this->testUser->givePermissionTo($this->testUserPermission, $this->testRestrictable1);

        $this->assertTrue($this->reloadPermissions());

        // This will search for a global permission and fail
        $this->assertFalse($this->testUser->can('edit-articles'));

        // This will search for a permission scoped to the wrong Restrictable instance and fail
        $this->assertFalse($this->testUser->can('edit-articles', $this->testRestrictable2));

        $this->assertTrue($this->testUser->can('edit-articles', $this->testRestrictable1));
    }

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
    public function it_can_determine_if_a_user_has_a_permission_when_using_scoped_roles()
    {
        $this->testUserRole->givePermissionTo($this->testUserPermission);

        $this->testUser->assignRole($this->testUserRole, $this->testRestrictable1);

        // This will search for a permission in a global role and fail
        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission));

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, $this->testRestrictable1));

        $this->assertTrue($this->reloadPermissions());

        // This will search for a global permission and fail
        $this->assertFalse($this->testUser->can('edit-articles'));

        // This will search for a permission scoped to the wrong Restrictable instance and fail
        $this->assertFalse($this->testUser->can('edit-articles', $this->testRestrictable2));

        $this->assertTrue($this->testUser->can('edit-articles', $this->testRestrictable1));
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
}
