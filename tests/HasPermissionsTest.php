<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class HasPermissionsTest extends TestCase
{
    /** @test */
    public function it_can_assign_a_permission_using_an_object()
    {
        $this->testUser->givePermissionTo($this->testUserPermission);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission));
    }

    /** @test */
    public function it_can_assign_a_permission_using_a_string()
    {
        $this->testUser->givePermissionTo('edit-articles');

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_can_assign_multiple_permissions_using_a_collection()
    {
        $collection = collect()->push($this->testUserPermission)->push('edit-articles');

        $this->testUser->givePermissionTo($collection);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission));

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_can_assign_multiple_permissions_using_an_array()
    {
        $this->testUser->assignRole(['edit-articles', 'edit-news']);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasRole('edit-articles'));

        $this->assertTrue($this->testUser->hasRole('edit-news'));
    }

    /** @test */
    public function it_can_assign_a_scoped_permission_to_a_user()
    {
        $this->testUser->givePermissionTo($this->testUserPermission, $this->testRestrictable1);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, $this->testRestrictable1));
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_permission_that_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->givePermissionTo('permission-does-not-exist');
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_permission_to_a_user_from_a_different_guard()
    {
        $this->expectException(GuardDoesNotMatch::class);

        $this->testUser->givePermissionTo($this->testAdminPermission);

        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->givePermissionTo('admin-permission');
    }

    /** @test */
    public function it_can_revoke_a_permission_from_a_user()
    {
        $this->testUser->givePermissionTo($this->testUserPermission);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission));

        $this->testUser->revokePermissionTo($this->testUserPermission);

        $this->refreshTestUser();

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission));
    }

    /** @test */
    public function it_can_revoke_a_scoped_permission_from_a_user()
    {
        $this->testUser->givePermissionTo($this->testUserPermission, $this->testRestrictable1);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission, $this->testRestrictable1));

        $this->testUser->revokePermissionTo($this->testUserPermission, $this->testRestrictable1);

        $this->refreshTestUser();

        $this->assertFalse($this->testUser->hasPermissionTo($this->testUserPermission, $this->testRestrictable1));
    }
}
