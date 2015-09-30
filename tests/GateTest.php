<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class GateTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_determine_if_a_user_has_a_permission_when_using_roles()
    {
        $this->testRole->givePermissionTo($this->testPermission);

        $this->testUser->assignRole($this->testRole);

        $this->assertTrue($this->testUser->hasPermissionTo($this->testPermission));

        $this->assertTrue($this->reloadPermissions());

        $this->assertTrue($this->testUser->can('edit-articles'));

        $this->assertFalse($this->testUser->can('non-existing-permission'));
    }

    /**
     * @test
     */
    public function it_can_determine_if_a_user_has_a_permission_when_direct_permissions()
    {
        $this->testUser->givePermissionTo($this->testPermission);

        $this->assertTrue($this->reloadPermissions());

        $this->assertTrue($this->testUser->can('edit-articles'));

        $this->assertFalse($this->testUser->can('non-existing-permission'));
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_when_using_a_permission_that_does_not_exist()
    {
        $this->setExpectedException(PermissionDoesNotExist::class);

        $this->testRole->givePermissionTo('create-evil-empire');
    }

    /**
     * @test
     */
    public function it_will_throw_an_exception_when_assign_a_role_that_does_not_exist()
    {
        $this->setExpectedException(RoleDoesNotExist::class);

        $this->testUser->assignRole('evil-emperor');
    }

    /**
     * @test
     */
    public function it_can_determine_if_a_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->can('edit-articles'));
    }
}
