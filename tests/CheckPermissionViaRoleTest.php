<?php

namespace Spatie\Permission\Test;

class CheckPermissionViaRoleTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_permission_via_role_true_assert_true()
    {
        config()->set('permission.check_permission_via_role', true);

        $this->testUserRole->givePermissionTo($this->testUserPermission);
        $this->testUser->assignRole($this->testUserRole);

        $this->assertTrue($this->testUser->checkPermissionTo($this->testUserPermission));
    }

    /** @test */
    public function check_permission_via_role_true_assert_false()
    {
        config()->set('permission.check_permission_via_role', true);

        $this->testUser->assignRole($this->testUserRole);

        $this->assertFalse($this->testUser->checkPermissionTo($this->testUserPermission));
    }

    /** @test */
    public function check_permission_via_role_false_assert_true()
    {
        config()->set('permission.check_permission_via_role', false);

        $this->testUserRole->givePermissionTo($this->testUserPermission);
        $this->testUser->givePermissionTo($this->testUserPermission);
        $this->testUser->assignRole($this->testUserRole);

        $this->assertTrue($this->testUserRole->checkPermissionTo($this->testUserPermission));
    }

    /** @test */
    public function check_permission_via_role_false_assert_false()
    {
        config()->set('permission.check_permission_via_role', false);

        $this->testUserRole->givePermissionTo($this->testUserPermission);
        $this->testUser->assignRole($this->testUserRole);

        $this->assertTrue($this->testUserRole->checkPermissionTo($this->testUserPermission));
    }
}
