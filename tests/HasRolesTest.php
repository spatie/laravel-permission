<?php

namespace Spatie\Permission\Test;

use DB;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class HasRolesTest extends TestCase
{
    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_role()
    {
        $this->assertFalse($this->testUser->hasRole('testRole'));

        $role = app(Role::class)->findOrCreate('testRoleInWebGuard', 'web');

        $this->assertFalse($this->testUser->hasRole($role));

        $this->testUser->assignRole($role);
        $this->assertTrue($this->testUser->hasRole($role));
        $this->assertTrue($this->testUser->hasRole($role->name));
        $this->assertTrue($this->testUser->hasRole($role->name, $role->guard_name));
        $this->assertTrue($this->testUser->hasRole([$role->name, 'fakeRole'], $role->guard_name));
        $this->assertTrue($this->testUser->hasRole($role->getKey(), $role->guard_name));
        $this->assertTrue($this->testUser->hasRole([$role->getKey(), 'fakeRole'], $role->guard_name));

        $this->assertFalse($this->testUser->hasRole($role->name, 'fakeGuard'));
        $this->assertFalse($this->testUser->hasRole([$role->name, 'fakeRole'], 'fakeGuard'));
        $this->assertFalse($this->testUser->hasRole($role->getKey(), 'fakeGuard'));
        $this->assertFalse($this->testUser->hasRole([$role->getKey(), 'fakeRole'], 'fakeGuard'));

        $role = app(Role::class)->findOrCreate('testRoleInWebGuard2', 'web');
        $this->assertFalse($this->testUser->hasRole($role));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role()
    {
        $this->assertFalse($this->testUser->hasRole('testRole'));

        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasRole('testRole'));

        $this->testUser->removeRole('testRole');

        $this->assertFalse($this->testUser->hasRole('testRole'));
    }

    /** @test */
    public function it_removes_a_role_and_returns_roles()
    {
        $this->testUser->assignRole('testRole');

        $this->testUser->assignRole('testRole2');

        $this->assertTrue($this->testUser->hasRole(['testRole', 'testRole2']));

        $roles = $this->testUser->removeRole('testRole');

        $this->assertFalse($roles->hasRole('testRole'));

        $this->assertTrue($roles->hasRole('testRole2'));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role_on_a_permission()
    {
        $this->testUserPermission->assignRole('testRole');

        $this->assertTrue($this->testUserPermission->hasRole('testRole'));

        $this->testUserPermission->removeRole('testRole');

        $this->assertFalse($this->testUserPermission->hasRole('testRole'));
    }

    /** @test */
    public function it_can_assign_a_role_using_an_object()
    {
        $this->testUser->assignRole($this->testUserRole);

        $this->assertTrue($this->testUser->hasRole($this->testUserRole));
    }

    /** @test */
    public function it_can_assign_a_role_using_an_id()
    {
        $this->testUser->assignRole($this->testUserRole->getKey());

        $this->assertTrue($this->testUser->hasRole($this->testUserRole));
    }

    /** @test */
    public function it_can_assign_multiple_roles_at_once()
    {
        $this->testUser->assignRole($this->testUserRole->getKey(), 'testRole2');

        $this->assertTrue($this->testUser->hasRole('testRole'));

        $this->assertTrue($this->testUser->hasRole('testRole2'));
    }

    /** @test */
    public function it_can_assign_multiple_roles_using_an_array()
    {
        $this->testUser->assignRole([$this->testUserRole->getKey(), 'testRole2']);

        $this->assertTrue($this->testUser->hasRole('testRole'));

        $this->assertTrue($this->testUser->hasRole('testRole2'));
    }

    /** @test */
    public function it_does_not_remove_already_associated_roles_when_assigning_new_roles()
    {
        $this->testUser->assignRole($this->testUserRole->getKey());

        $this->testUser->assignRole('testRole2');

        $this->assertTrue($this->testUser->fresh()->hasRole('testRole'));
    }

    /** @test */
    public function it_does_not_throw_an_exception_when_assigning_a_role_that_is_already_assigned()
    {
        $this->testUser->assignRole($this->testUserRole->getKey());

        $this->testUser->assignRole($this->testUserRole->getKey());

        $this->assertTrue($this->testUser->fresh()->hasRole('testRole'));
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_role_that_does_not_exist()
    {
        $this->expectException(RoleDoesNotExist::class);

        $this->testUser->assignRole('evil-emperor');
    }

    /** @test */
    public function it_can_only_assign_roles_from_the_correct_guard()
    {
        $this->expectException(RoleDoesNotExist::class);

        $this->testUser->assignRole('testAdminRole');
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_role_from_a_different_guard()
    {
        $this->expectException(GuardDoesNotMatch::class);

        $this->testUser->assignRole($this->testAdminRole);
    }

    /** @test */
    public function it_ignores_null_roles_when_syncing()
    {
        $this->testUser->assignRole('testRole');

        $this->testUser->syncRoles('testRole2', null);

        $this->assertFalse($this->testUser->hasRole('testRole'));

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
    public function it_can_sync_roles_from_a_string_on_a_permission()
    {
        $this->testUserPermission->assignRole('testRole');

        $this->testUserPermission->syncRoles('testRole2');

        $this->assertFalse($this->testUserPermission->hasRole('testRole'));

        $this->assertTrue($this->testUserPermission->hasRole('testRole2'));
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
    public function it_will_remove_all_roles_when_an_empty_array_is_passed_to_sync_roles()
    {
        $this->testUser->assignRole('testRole');

        $this->testUser->assignRole('testRole2');

        $this->testUser->syncRoles([]);

        $this->assertFalse($this->testUser->hasRole('testRole'));

        $this->assertFalse($this->testUser->hasRole('testRole2'));
    }

    /** @test */
    public function it_will_sync_roles_to_a_model_that_is_not_persisted()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->syncRoles([$this->testUserRole]);
        $user->save();

        $this->assertTrue($user->hasRole($this->testUserRole));
    }

    /** @test */
    public function calling_syncRoles_before_saving_object_doesnt_interfere_with_other_objects()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->syncRoles('testRole');
        $user->save();

        $user2 = new User(['email' => 'admin@user.com']);
        $user2->syncRoles('testRole2');

        DB::enableQueryLog();
        $user2->save();
        DB::disableQueryLog();

        $this->assertTrue($user->fresh()->hasRole('testRole'));
        $this->assertFalse($user->fresh()->hasRole('testRole2'));

        $this->assertTrue($user2->fresh()->hasRole('testRole2'));
        $this->assertFalse($user2->fresh()->hasRole('testRole'));
        $this->assertSame(4, count(DB::getQueryLog())); //avoid unnecessary sync
    }

    /** @test */
    public function calling_assignRole_before_saving_object_doesnt_interfere_with_other_objects()
    {
        $user = new User(['email' => 'test@user.com']);
        $user->assignRole('testRole');
        $user->save();

        $admin_user = new User(['email' => 'admin@user.com']);
        $admin_user->assignRole('testRole2');

        DB::enableQueryLog();
        $admin_user->save();
        DB::disableQueryLog();

        $this->assertTrue($user->fresh()->hasRole('testRole'));
        $this->assertFalse($user->fresh()->hasRole('testRole2'));

        $this->assertTrue($admin_user->fresh()->hasRole('testRole2'));
        $this->assertFalse($admin_user->fresh()->hasRole('testRole'));
        $this->assertSame(4, count(DB::getQueryLog())); //avoid unnecessary sync
    }

    /** @test */
    public function it_throws_an_exception_when_syncing_a_role_from_another_guard()
    {
        $this->expectException(RoleDoesNotExist::class);

        $this->testUser->syncRoles('testRole', 'testAdminRole');

        $this->expectException(GuardDoesNotMatch::class);

        $this->testUser->syncRoles('testRole', $this->testAdminRole);
    }

    /** @test */
    public function it_deletes_pivot_table_entries_when_deleting_models()
    {
        $user = User::create(['email' => 'user@test.com']);

        $user->assignRole('testRole');
        $user->givePermissionTo('edit-articles');

        $this->assertDatabaseHas('model_has_permissions', [config('permission.column_names.model_morph_key') => $user->id]);
        $this->assertDatabaseHas('model_has_roles', [config('permission.column_names.model_morph_key') => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing('model_has_permissions', [config('permission.column_names.model_morph_key') => $user->id]);
        $this->assertDatabaseMissing('model_has_roles', [config('permission.column_names.model_morph_key') => $user->id]);
    }

    /** @test */
    public function it_can_scope_users_using_a_string()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole('testRole');
        $user2->assignRole('testRole2');

        $scopedUsers = User::role('testRole')->get();

        $this->assertEquals(1, $scopedUsers->count());
    }

    /** @test */
    public function it_can_scope_users_using_an_array()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole($this->testUserRole);
        $user2->assignRole('testRole2');

        $scopedUsers1 = User::role([$this->testUserRole])->get();

        $scopedUsers2 = User::role(['testRole', 'testRole2'])->get();

        $this->assertEquals(1, $scopedUsers1->count());
        $this->assertEquals(2, $scopedUsers2->count());
    }

    /** @test */
    public function it_can_scope_users_using_an_array_of_ids_and_names()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);

        $user1->assignRole($this->testUserRole);

        $user2->assignRole('testRole2');

        $roleName = $this->testUserRole->name;

        $otherRoleId = app(Role::class)->findByName('testRole2')->getKey();

        $scopedUsers = User::role([$roleName, $otherRoleId])->get();

        $this->assertEquals(2, $scopedUsers->count());
    }

    /** @test */
    public function it_can_scope_users_using_a_collection()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole($this->testUserRole);
        $user2->assignRole('testRole2');

        $scopedUsers1 = User::role([$this->testUserRole])->get();
        $scopedUsers2 = User::role(collect(['testRole', 'testRole2']))->get();

        $this->assertEquals(1, $scopedUsers1->count());
        $this->assertEquals(2, $scopedUsers2->count());
    }

    /** @test */
    public function it_can_scope_users_using_an_object()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole($this->testUserRole);
        $user2->assignRole('testRole2');

        $scopedUsers1 = User::role($this->testUserRole)->get();
        $scopedUsers2 = User::role([$this->testUserRole])->get();
        $scopedUsers3 = User::role(collect([$this->testUserRole]))->get();

        $this->assertEquals(1, $scopedUsers1->count());
        $this->assertEquals(1, $scopedUsers2->count());
        $this->assertEquals(1, $scopedUsers3->count());
    }

    /** @test */
    public function it_can_scope_against_a_specific_guard()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignRole('testRole');
        $user2->assignRole('testRole2');

        $scopedUsers1 = User::role('testRole', 'web')->get();

        $this->assertEquals(1, $scopedUsers1->count());

        $user3 = Admin::create(['email' => 'user1@test.com']);
        $user4 = Admin::create(['email' => 'user1@test.com']);
        $user5 = Admin::create(['email' => 'user2@test.com']);
        $testAdminRole2 = app(Role::class)->create(['name' => 'testAdminRole2', 'guard_name' => 'admin']);
        $user3->assignRole($this->testAdminRole);
        $user4->assignRole($this->testAdminRole);
        $user5->assignRole($testAdminRole2);
        $scopedUsers2 = Admin::role('testAdminRole', 'admin')->get();
        $scopedUsers3 = Admin::role('testAdminRole2', 'admin')->get();

        $this->assertEquals(2, $scopedUsers2->count());
        $this->assertEquals(1, $scopedUsers3->count());
    }

    /** @test */
    public function it_throws_an_exception_when_trying_to_scope_a_role_from_another_guard()
    {
        $this->expectException(RoleDoesNotExist::class);

        User::role('testAdminRole')->get();

        $this->expectException(GuardDoesNotMatch::class);

        User::role($this->testAdminRole)->get();
    }

    /** @test */
    public function it_throws_an_exception_when_trying_to_scope_a_non_existing_role()
    {
        $this->expectException(RoleDoesNotExist::class);

        User::role('role not defined')->get();
    }

    /** @test */
    public function it_can_determine_that_a_user_has_one_of_the_given_roles()
    {
        $roleModel = app(Role::class);

        $roleModel->create(['name' => 'second role']);

        $this->assertFalse($this->testUser->hasRole($roleModel->all()));

        $this->testUser->assignRole($this->testUserRole);

        $this->assertTrue($this->testUser->hasRole($roleModel->all()));

        $this->assertTrue($this->testUser->hasAnyRole($roleModel->all()));

        $this->assertTrue($this->testUser->hasAnyRole('testRole'));

        $this->assertFalse($this->testUser->hasAnyRole('role does not exist'));

        $this->assertTrue($this->testUser->hasAnyRole(['testRole']));

        $this->assertTrue($this->testUser->hasAnyRole(['testRole', 'role does not exist']));

        $this->assertFalse($this->testUser->hasAnyRole(['role does not exist']));

        $this->assertTrue($this->testUser->hasAnyRole('testRole', 'role does not exist'));
    }

    /** @test */
    public function it_can_determine_that_a_user_has_all_of_the_given_roles()
    {
        $roleModel = app(Role::class);

        $this->assertFalse($this->testUser->hasAllRoles($roleModel->first()));

        $this->assertFalse($this->testUser->hasAllRoles('testRole'));

        $this->assertFalse($this->testUser->hasAllRoles($roleModel->all()));

        $roleModel->create(['name' => 'second role']);

        $this->testUser->assignRole($this->testUserRole);

        $this->assertTrue($this->testUser->hasAllRoles('testRole'));
        $this->assertTrue($this->testUser->hasAllRoles('testRole', 'web'));
        $this->assertFalse($this->testUser->hasAllRoles('testRole', 'fakeGuard'));

        $this->assertFalse($this->testUser->hasAllRoles(['testRole', 'second role']));
        $this->assertFalse($this->testUser->hasAllRoles(['testRole', 'second role'], 'web'));

        $this->testUser->assignRole('second role');

        $this->assertTrue($this->testUser->hasAllRoles(['testRole', 'second role']));
        $this->assertTrue($this->testUser->hasAllRoles(['testRole', 'second role'], 'web'));
        $this->assertFalse($this->testUser->hasAllRoles(['testRole', 'second role'], 'fakeGuard'));
    }

    /** @test */
    public function it_can_determine_that_a_user_has_exact_all_of_the_given_roles()
    {
        $roleModel = app(Role::class);

        $this->assertFalse($this->testUser->hasExactRoles($roleModel->first()));

        $this->assertFalse($this->testUser->hasExactRoles('testRole'));

        $this->assertFalse($this->testUser->hasExactRoles($roleModel->all()));

        $roleModel->create(['name' => 'second role']);

        $this->testUser->assignRole($this->testUserRole);

        $this->assertTrue($this->testUser->hasExactRoles('testRole'));
        $this->assertTrue($this->testUser->hasExactRoles('testRole', 'web'));
        $this->assertFalse($this->testUser->hasExactRoles('testRole', 'fakeGuard'));

        $this->assertFalse($this->testUser->hasExactRoles(['testRole', 'second role']));
        $this->assertFalse($this->testUser->hasExactRoles(['testRole', 'second role'], 'web'));

        $this->testUser->assignRole('second role');

        $this->assertTrue($this->testUser->hasExactRoles(['testRole', 'second role']));
        $this->assertTrue($this->testUser->hasExactRoles(['testRole', 'second role'], 'web'));
        $this->assertFalse($this->testUser->hasExactRoles(['testRole', 'second role'], 'fakeGuard'));

        $roleModel->create(['name' => 'third role']);
        $this->testUser->assignRole('third role');

        $this->assertFalse($this->testUser->hasExactRoles(['testRole', 'second role']));
        $this->assertFalse($this->testUser->hasExactRoles(['testRole', 'second role'], 'web'));
        $this->assertFalse($this->testUser->hasExactRoles(['testRole', 'second role'], 'fakeGuard'));
        $this->assertTrue($this->testUser->hasExactRoles(['testRole', 'second role', 'third role']));
        $this->assertTrue($this->testUser->hasExactRoles(['testRole', 'second role', 'third role'], 'web'));
        $this->assertFalse($this->testUser->hasExactRoles(['testRole', 'second role', 'third role'], 'fakeGuard'));
    }

    /** @test */
    public function it_can_determine_that_a_user_does_not_have_a_role_from_another_guard()
    {
        $this->assertFalse($this->testUser->hasRole('testAdminRole'));

        $this->assertFalse($this->testUser->hasRole($this->testAdminRole));

        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasAnyRole(['testRole', 'testAdminRole']));

        $this->assertFalse($this->testUser->hasAnyRole('testAdminRole', $this->testAdminRole));
    }

    /** @test */
    public function it_can_check_against_any_multiple_roles_using_multiple_arguments()
    {
        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasAnyRole($this->testAdminRole, ['testRole'], 'This Role Does Not Even Exist'));
    }

    /** @test */
    public function it_returns_false_instead_of_an_exception_when_checking_against_any_undefined_roles_using_multiple_arguments()
    {
        $this->assertFalse($this->testUser->hasAnyRole('This Role Does Not Even Exist', $this->testAdminRole));
    }

    /** @test */
    public function it_can_retrieve_role_names()
    {
        $this->testUser->assignRole('testRole', 'testRole2');

        $this->assertEquals(
            collect(['testRole', 'testRole2']),
            $this->testUser->getRoleNames()->sort()->values()
        );
    }

    /** @test */
    public function it_does_not_detach_roles_when_soft_deleting()
    {
        $user = SoftDeletingUser::create(['email' => 'test@example.com']);
        $user->assignRole('testRole');
        $user->delete();

        $user = SoftDeletingUser::withTrashed()->find($user->id);

        $this->assertTrue($user->hasRole('testRole'));
    }
}
