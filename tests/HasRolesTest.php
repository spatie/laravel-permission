<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Models\Role;

class HasRolesTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_determine_that_the_user_does_not_have_a_role()
    {
        $this->assertFalse($this->testUser->hasRole('testRole'));
    }

    /**
     * @test
     */
    public function it_can_assign_and_remove_a_role()
    {
        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasRole('testRole'));

        $this->testUser->removeRole('testRole');

        $this->refreshTestUser();

        $this->assertFalse($this->testUser->hasRole('testRole'));
    }

    /**
     * @test
     */
    public function it_can_assign_a_role_using_an_object()
    {
        $this->testUser->assignRole($this->testRole);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasRole($this->testRole));
    }

    /**
     * @test
     */
    public function it_can_determine_that_a_user_has_one_of_the_given_roles()
    {
        Role::create(['name' => 'second role']);

        $this->assertFalse($this->testUser->hasRole(Role::all()));

        $this->testUser->assignRole($this->testRole);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasRole(Role::all()));

        $this->assertTrue($this->testUser->hasAnyRole(Role::all()));
    }

    /**
     * @test
     */
    public function it_can_determine_that_a_user_has_all_of_the_given_roles()
    {
        $this->assertFalse($this->testUser->hasAllRoles(Role::first()));

        $this->assertFalse($this->testUser->hasAllRoles('testRole'));

        $this->assertFalse($this->testUser->hasAllRoles(Role::all()));

		Role::create(['name' => 'second role']);
		
        $this->testUser->assignRole($this->testRole);

		$this->refreshTestUser();

		$this->assertFalse($this->testUser->hasAllRoles(Role::all()));

        $this->testUser->assignRole('second role');

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAllRoles(Role::all()));
    }

    /**
     * @test
     */
    public function it_can_determine_that_the_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->hasPermissionTo('edit-articles'));
    }

    /**
     * @test
     */
    public function it_can_assign_a_permission_to_a_role()
    {
        $this->testRole->givePermissionTo('edit-articles');

        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
    }

    /**
     * @test
     */
    public function it_can_revoke_a_permission_from_a_role()
    {
        $this->testRole->givePermissionTo('edit-articles');

        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));

        $this->testRole->revokePermissionTo('edit-articles');

        $this->assertFalse($this->testUser->hasPermissionTo('edit-articles'));
    }

    /**
     * @test
     */
    public function it_can_assign_a_permission_to_a_role_using_objects()
    {
        $this->testRole->givePermissionTo($this->testPermission);

        $this->testUser->assignRole($this->testRole);

        $this->assertTrue($this->testUser->hasPermissionTo($this->testPermission));
    }

    /**
     * @test
     */
    public function it_can_assign_a_permission_to_a_user()
    {
        $this->testUser->givePermissionTo($this->testPermission);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasPermissionTo($this->testPermission));
    }

    /**
     * @test
     */
    public function it_can_revoke_a_permission_from_a_user()
    {
        $this->testUser->givePermissionTo($this->testPermission);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasPermissionTo($this->testPermission));

        $this->testUser->revokePermissionTo($this->testPermission);

        $this->refreshTestUser();

        $this->assertFalse($this->testUser->hasPermissionTo($this->testPermission));
    }

    /**
     * @test
     *
     * @deprecated
     */
    public function it_can_check_permissions_with_the_deprecated_has_permission_method()
    {
        $this->assertSame(
            $this->testUser->hasPermissionTo($this->testPermission),
            $this->testUser->hasPermission($this->testPermission)
        );

        $this->testUser->givePermissionTo($this->testPermission);

        $this->refreshTestUser();

        $this->assertSame(
            $this->testUser->hasPermissionTo($this->testPermission),
            $this->testUser->hasPermission($this->testPermission)
        );
    }
}
