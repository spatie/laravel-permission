<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class HasRolesTest extends TestCase
{
    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_role()
    {
        $this->assertFalse($this->testUser->hasRole('testRole'));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role()
    {
        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasRole('testRole'));

        $this->testUser->removeRole('testRole');

        $this->refreshTestUser();

        $this->assertFalse($this->testUser->hasRole('testRole'));
    }

    /** @test */
    public function it_can_assign_multiple_roles_at_once()
    {
        $this->testUser->assignRole('testRole', 'testRole2');

        $this->assertTrue($this->testUser->hasRole('testRole'));

        $this->assertTrue($this->testUser->hasRole('testRole2'));
    }

    /** @test */
    public function it_can_assign_multiple_roles_using_an_array()
    {
        $this->testUser->assignRole(['testRole', 'testRole2']);

        $this->assertTrue($this->testUser->hasRole('testRole'));

        $this->assertTrue($this->testUser->hasRole('testRole2'));
    }

    /** @test */
    public function it_can_sync_roles_from_a_string()
    {
        $this->testUser->assignRole('testRole');

        $this->testUser->syncRoles('testRole2');

        $this->assertFalse($this->testUser->hasRole('testRole'));

        $this->assertTrue($this->testUser->hasRole('testRole2'));
    }

    /** @test */
    public function it_can_sync_multiple_roles()
    {
        $this->testUser->syncRoles('testRole', 'testRole2');

        $this->assertTrue($this->testUser->hasRole('testRole'));

        $this->assertTrue($this->testUser->hasRole('testRole2'));
    }

    /** @test */
    public function it_can_sync_multiple_roles_from_an_array()
    {
        $this->testUser->syncRoles(['testRole', 'testRole2']);

        $this->assertTrue($this->testUser->hasRole('testRole'));

        $this->assertTrue($this->testUser->hasRole('testRole2'));
    }

    /** @test */
    public function it_will_remove_all_roles_when_an_empty_array_is_past_to_sync_roles()
    {
        $this->testUser->assignRole('testRole');

        $this->testUser->assignRole('testRole2');

        $this->testUser->syncRoles([]);

        $this->assertFalse($this->testUser->hasRole('testRole'));

        $this->assertFalse($this->testUser->hasRole('testRole2'));
    }

    /** @test */
    public function it_can_assign_a_role_using_an_object()
    {
        $this->testUser->assignRole($this->testRole);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasRole($this->testRole));
    }

    /** @test */
    public function it_can_scope_users_using_a_string()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole('testRole');
        $user2->assignRole('testRole2');

        $scopedUsers = User::role('testRole')->get();

        $this->assertEquals($scopedUsers->count(), 1);
    }

    /** @test */
    public function it_can_scope_users_using_an_array()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole($this->testRole);
        $user2->assignRole('testRole2');

        $scopedUsers1 = User::role([$this->testRole])->get();
        $scopedUsers2 = User::role(['testRole', 'testRole2'])->get();

        $this->assertEquals($scopedUsers1->count(), 1);
        $this->assertEquals($scopedUsers2->count(), 2);
    }

    /** @test */
    public function it_can_scope_users_using_a_collection()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole($this->testRole);
        $user2->assignRole('testRole2');

        $scopedUsers1 = User::role([$this->testRole])->get();
        $scopedUsers2 = User::role(collect(['testRole', 'testRole2']))->get();

        $this->assertEquals($scopedUsers1->count(), 1);
        $this->assertEquals($scopedUsers2->count(), 2);
    }

    /** @test */
    public function it_can_scope_users_using_an_object()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole($this->testRole);
        $user2->assignRole('testRole2');

        $scopedUsers = User::role($this->testRole)->get();

        $this->assertEquals($scopedUsers->count(), 1);
    }

    /** @test */
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

    /** @test */
    public function it_can_determine_that_a_user_has_all_of_the_given_roles()
    {
        $roleModel = app(Role::class);

        $this->assertFalse($this->testUser->hasAllRoles($roleModel->first()));

        $this->assertFalse($this->testUser->hasAllRoles('testRole'));

        $this->assertFalse($this->testUser->hasAllRoles($roleModel->all()));

        $roleModel->create(['name' => 'second role']);

        $this->testUser->assignRole($this->testRole);

        $this->refreshTestUser();

        $this->assertFalse($this->testUser->hasAllRoles(['testRole', 'second role']));

        $this->testUser->assignRole('second role');

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAllRoles(['testRole', 'second role']));
    }

    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_can_work_with_a_user_that_does_not_have_any_permissions_at_all()
    {
        $user = new User();

        $this->assertFalse($user->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_permission_even_with_non_existing_permissions()
    {
        $this->setExpectedException(PermissionDoesNotExist::class);

        $this->assertFalse($this->testUser->hasPermissionTo('this permission does not exists'));
    }

    /** @test */
    public function it_can_assign_a_permission_to_a_role()
    {
        $this->testRole->givePermissionTo('edit-articles');

        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_can_assign_multiple_permissions_to_a_role_using_an_array()
    {
        $this->testRole->givePermissionTo(['edit-articles', 'edit-news']);

        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
        $this->assertTrue($this->testUser->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function it_can_assign_multiple_permissions_to_a_role_using_multiple_arguments()
    {
        $this->testRole->givePermissionTo('edit-articles', 'edit-news');

        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));
        $this->assertTrue($this->testUser->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function it_can_sync_permissions_on_a_role()
    {
        $this->testRole->givePermissionTo('edit-articles');

        $this->testRole->syncPermissions('edit-news');

        $this->testUser->assignRole('testRole');

        $this->assertFalse($this->testUser->hasPermissionTo('edit-articles'));

        $this->assertTrue($this->testUser->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function it_will_remove_all_permission_on_a_role_when_passing_an_empty_array_to_sync_permissions()
    {
        $this->testRole->givePermissionTo('edit-articles');

        $this->testRole->givePermissionTo('edit-news');

        $this->testRole->syncPermissions([]);

        $this->testUser->assignRole('testRole');

        $this->assertFalse($this->testUser->hasPermissionTo('edit-articles'));

        $this->assertFalse($this->testUser->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function it_can_revoke_a_permission_from_a_role()
    {
        $this->testRole->givePermissionTo('edit-articles');

        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasPermissionTo('edit-articles'));

        $this->testRole->revokePermissionTo('edit-articles');

        $this->assertFalse($this->testUser->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_can_assign_a_permission_to_a_role_using_objects()
    {
        $this->testRole->givePermissionTo($this->testPermission);

        $this->testUser->assignRole($this->testRole);

        $this->assertTrue($this->testUser->hasPermissionTo($this->testPermission));
    }

    /** @test */
    public function it_can_assign_a_permission_to_a_user()
    {
        $this->testUser->givePermissionTo($this->testPermission);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasPermissionTo($this->testPermission));
    }

    /** @test */
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
