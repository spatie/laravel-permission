<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

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
        $roleModel = app(Role::class);

        $roleModel->create(['name' => 'second role']);

        $this->assertFalse($this->testUser->hasRole($roleModel->all()));

        $this->testUser->assignRole($this->testRole);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasRole($roleModel->all()));

        $this->assertTrue($this->testUser->hasAnyRole($roleModel->all()));

        $this->assertTrue($this->testUser->hasAnyRole('testRole'));

        $this->assertFalse($this->testUser->hasAnyRole('role does not exist'));

        $this->assertTrue($this->testUser->hasAnyRole(['testRole']));

        $this->assertTrue($this->testUser->hasAnyRole(['testRole', 'role does not exist']));

        $this->assertFalse($this->testUser->hasAnyRole(['role does not exist']));
    }

    /**
     * @test
     */
    public function it_can_determine_that_a_user_has_all_of_the_given_roles()
    {
        $roleModel = app(Role::class);

        $this->assertFalse($this->testUser->hasAllRoles($roleModel->first()));

        $this->assertFalse($this->testUser->hasAllRoles('testRole'));

        $this->assertFalse($this->testUser->hasAllRoles($roleModel->all()));

        $roleModel->create(['name' => 'second role']);

        $this->testUser->assignRole($this->testRole);

        $this->refreshTestUser();

        $this->assertFalse($this->testUser->hasAllRoles($roleModel->all()));

        $this->testUser->assignRole('second role');

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAllRoles($roleModel->all()));
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
    public function it_can_work_with_a_user_that_does_not_have_any_permissions_at_all()
    {
        $user = new User();

        $this->assertFalse($user->hasPermissionTo('edit-articles'));
    }

    /**
     * @test
     */
    public function it_can_determine_that_the_user_does_not_have_a_permission_even_with_non_existing_permissions()
    {
        $this->setExpectedException(PermissionDoesNotExist::class);

        $this->assertFalse($this->testUser->hasPermissionTo('this permission does not exists'));
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
    public function it_can_assign_multiple_permissions_to_a_role_using_an_array()
    {
        $this->testRole->givePermissionTo(['edit-articles', 'edit-news']);

        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
        $this->assertTrue($this->testUser->hasPermissionTo('edit-news'));
    }

    /**
     * @test
     */
    public function it_can_assign_multiple_permissions_to_a_role_using_multiple_arguments()
    {
        $this->testRole->givePermissionTo('edit-articles', 'edit-news');

        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
        $this->assertTrue($this->testUser->hasPermissionTo('edit-news'));
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
