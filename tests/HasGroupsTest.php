<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Contracts\Group;
use Spatie\Permission\Exceptions\GroupDoesNotExist;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class HasGroupsTest extends TestCase
{
    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_group()
    {
        $this->assertFalse($this->testUser->hasGroup('testGroup'));
    }

    /** @test */
    public function it_can_assign_and_remove_a_group()
    {
        $this->testUser->assignGroup('testGroup');

        $this->assertTrue($this->testUser->hasGroup('testGroup'));

        $this->testUser->removeGroup('testGroup');

        $this->refreshTestUser();

        $this->assertFalse($this->testUser->hasGroup('testGroup'));
    }

    /** @test */
    public function it_can_assign_a_group_using_an_object()
    {
        $this->testUser->assignGroup($this->testUserGroup);

        $this->assertTrue($this->testUser->hasGroup($this->testUserGroup));
    }

    /** @test */
    public function it_can_assign_multiple_groups_at_once()
    {
        $this->testUser->assignGroup('testGroup', 'testGroup2');

        $this->assertTrue($this->testUser->hasGroup('testGroup'));

        $this->assertTrue($this->testUser->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_can_assign_multiple_groups_using_an_array()
    {
        $this->testUser->assignGroup(['testGroup', 'testGroup2']);

        $this->assertTrue($this->testUser->hasGroup('testGroup'));

        $this->assertTrue($this->testUser->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_group_that_does_not_exist()
    {
        $this->expectException(GroupDoesNotExist::class);

        $this->testUser->assignGroup('evil-emperor');
    }

    /** @test */
    public function it_can_only_assign_groups_from_the_correct_guard()
    {
        $this->expectException(GroupDoesNotExist::class);

        $this->testUser->assignGroup('testAdminGroup');
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_group_from_a_different_guard()
    {
        $this->expectException(GuardDoesNotMatch::class);

        $this->testUser->assignGroup($this->testAdminGroup);
    }

    /** @test */
    public function it_can_sync_groups_from_a_string()
    {
        $this->testUser->assignGroup('testGroup');

        $this->testUser->syncGroups('testGroup2');

        $this->assertFalse($this->testUser->hasGroup('testGroup'));

        $this->assertTrue($this->testUser->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_can_sync_multiple_groups()
    {
        $this->testUser->syncGroups('testGroup', 'testGroup2');

        $this->assertTrue($this->testUser->hasGroup('testGroup'));

        $this->assertTrue($this->testUser->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_can_sync_multiple_groups_from_an_array()
    {
        $this->testUser->syncGroups(['testGroup', 'testGroup2']);

        $this->assertTrue($this->testUser->hasGroup('testGroup'));

        $this->assertTrue($this->testUser->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_will_remove_all_groups_when_an_empty_array_is_past_to_sync_groups()
    {
        $this->testUser->assignGroup('testGroup');

        $this->testUser->assignGroup('testGroup2');

        $this->testUser->syncGroups([]);

        $this->assertFalse($this->testUser->hasGroup('testGroup'));

        $this->assertFalse($this->testUser->hasGroup('testGroup2'));
    }

    /** @test */
    public function it_throws_an_exception_when_syncing_a_group_from_another_guard()
    {
        $this->expectException(GroupDoesNotExist::class);

        $this->testUser->syncGroups('testGroup', 'testAdminGroup');

        $this->expectException(GuardDoesNotMatch::class);

        $this->testUser->syncGroups('testGroup', $this->testAdminGroup);
    }

    /** @test */
    public function it_deletes_pivot_table_entries_when_deleting_models()
    {
        $user = User::create(['email' => 'user@test.com']);

        $user->assignGroup('testGroup');
        $user->givePermissionTo('edit-articles');

        $this->assertDatabaseHas('model_has_permissions', ['model_id' => $user->id]);
        $this->assertDatabaseHas('model_has_groups', ['model_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseMissing('model_has_permissions', ['model_id' => $user->id]);
        $this->assertDatabaseMissing('model_has_groups', ['model_id' => $user->id]);
    }

    /** @test */
    public function it_can_scope_users_using_a_string()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignGroup('testGroup');
        $user2->assignGroup('testGroup2');

        $scopedUsers = User::group('testGroup')->get();

        $this->assertEquals($scopedUsers->count(), 1);
    }

    /** @test */
    public function it_can_scope_users_using_an_array()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignGroup($this->testUserGroup);
        $user2->assignGroup('testGroup2');

        $scopedUsers1 = User::group([$this->testUserGroup])->get();
        $scopedUsers2 = User::group(['testGroup', 'testGroup2'])->get();

        $this->assertEquals($scopedUsers1->count(), 1);
        $this->assertEquals($scopedUsers2->count(), 2);
    }

    /** @test */
    public function it_can_scope_users_using_a_collection()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignGroup($this->testUserGroup);
        $user2->assignGroup('testGroup2');

        $scopedUsers1 = User::group([$this->testUserGroup])->get();
        $scopedUsers2 = User::group(collect(['testGroup', 'testGroup2']))->get();

        $this->assertEquals($scopedUsers1->count(), 1);
        $this->assertEquals($scopedUsers2->count(), 2);
    }

    /** @test */
    public function it_can_scope_users_using_an_object()
    {
        $user1 = User::create(['email' => 'user1@test.com']);
        $user2 = User::create(['email' => 'user2@test.com']);
        $user1->assignGroup($this->testUserGroup);
        $user2->assignGroup('testGroup2');

        $scopedUsers = User::group($this->testUserGroup)->get();

        $this->assertEquals($scopedUsers->count(), 1);
    }

    /** @test */
    public function it_throws_an_exception_when_trying_to_scope_a_group_from_another_guard()
    {
        $this->expectException(GroupDoesNotExist::class);

        User::group('testAdminGroup')->get();

        $this->expectException(GuardDoesNotMatch::class);

        User::group($this->testAdminGroup)->get();
    }

    /** @test */
    public function it_can_determine_that_a_user_has_one_of_the_given_groups()
    {
        $groupModel = app(Group::class);

        $groupModel->create(['name' => 'second group']);

        $this->assertFalse($this->testUser->hasGroup($groupModel->all()));

        $this->testUser->assignGroup($this->testUserGroup);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasGroup($groupModel->all()));

        $this->assertTrue($this->testUser->hasAnyGroup($groupModel->all()));

        $this->assertTrue($this->testUser->hasAnyGroup('testGroup'));

        $this->assertFalse($this->testUser->hasAnyGroup('group does not exist'));

        $this->assertTrue($this->testUser->hasAnyGroup(['testGroup']));

        $this->assertTrue($this->testUser->hasAnyGroup(['testGroup', 'group does not exist']));

        $this->assertFalse($this->testUser->hasAnyGroup(['group does not exist']));

        $this->assertTrue($this->testUser->hasAnyGroup('testGroup', 'group does not exist'));
    }

    /** @test */
    public function it_can_determine_that_a_user_has_all_of_the_given_groups()
    {
        $groupModel = app(Group::class);

        $this->assertFalse($this->testUser->hasAllGroups($groupModel->first()));

        $this->assertFalse($this->testUser->hasAllGroups('testGroup'));

        $this->assertFalse($this->testUser->hasAllGroups($groupModel->all()));

        $groupModel->create(['name' => 'second group']);

        $this->testUser->assignGroup($this->testUserGroup);

        $this->refreshTestUser();

        $this->assertFalse($this->testUser->hasAllGroups(['testGroup', 'second group']));

        $this->testUser->assignGroup('second group');

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAllGroups(['testGroup', 'second group']));
    }

    /** @test */
    public function it_can_determine_that_a_user_does_not_have_a_group_from_another_guard()
    {
        $this->assertFalse($this->testUser->hasGroup('testAdminGroup'));

        $this->assertFalse($this->testUser->hasGroup($this->testAdminGroup));

        $this->testUser->assignGroup('testGroup');

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAnyGroup(['testGroup', 'testAdminGroup']));

        $this->assertFalse($this->testUser->hasAnyGroup('testAdminGroup', $this->testAdminGroup));
    }

    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_throws_an_exception_when_the_permission_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->hasPermissionTo('does-not-exist');
    }

    /** @test */
    public function it_throws_an_exception_when_the_permission_does_not_exist_for_this_guard()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUser->hasPermissionTo('admin-permission');
    }

    /** @test */
    public function it_can_work_with_a_user_that_does_not_have_any_permissions_at_all()
    {
        $user = new User();

        $this->assertFalse($user->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_any_of_the_permissions_directly()
    {
        $this->assertFalse($this->testUser->hasAnyPermission('edit-articles'));

        $this->testUser->givePermissionTo('edit-articles');

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAnyPermission('edit-news', 'edit-articles'));

        $this->testUser->givePermissionTo('edit-news');

        $this->refreshTestUser();

        $this->testUser->revokePermissionTo($this->testUserPermission);

        $this->assertTrue($this->testUser->hasAnyPermission('edit-articles', 'edit-news'));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_any_of_the_permissions_directly_using_an_array()
    {
        $this->assertFalse($this->testUser->hasAnyPermission(['edit-articles']));

        $this->testUser->givePermissionTo('edit-articles');

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAnyPermission(['edit-news', 'edit-articles']));

        $this->testUser->givePermissionTo('edit-news');

        $this->refreshTestUser();

        $this->testUser->revokePermissionTo($this->testUserPermission);

        $this->assertTrue($this->testUser->hasAnyPermission(['edit-articles', 'edit-news']));
    }

    /** @test */
    public function it_can_determine_that_the_user_has_any_of_the_permissions_via_group()
    {
        $this->testUserGroup->givePermissionTo('edit-articles');

        $this->testUser->assignGroup('testGroup');

        $this->assertTrue($this->testUser->hasAnyPermission('edit-news', 'edit-articles'));
    }

    /** @test */
    public function it_can_determine_that_user_has_direct_permission()
    {
        $this->testUser->givePermissionTo('edit-articles');
        $this->refreshTestUser();
        $this->assertTrue($this->testUser->hasDirectPermission('edit-articles'));
        $this->testUser->revokePermissionTo('edit-articles');
        $this->refreshTestUser();
        $this->assertFalse($this->testUser->hasDirectPermission('edit-articles'));

        $this->testUser->assignGroup('testGroup');
        $this->testUserGroup->givePermissionTo('edit-articles');
        $this->refreshTestUser();
        $this->assertFalse($this->testUser->hasDirectPermission('edit-articles'));
    }

    /** @test */
    public function it_can_list_all_the_permissions_via_his_groups()
    {
        $groupModel = app(Group::class);
        $groupModel->findByName('testGroup2')->givePermissionTo('edit-news');

        $this->testUserGroup->givePermissionTo('edit-articles');
        $this->testUser->assignGroup('testGroup', 'testGroup2');

        $this->assertEquals(
            collect(['edit-articles', 'edit-news']),
            $this->testUser->getPermissionsViaGroups()->pluck('name')
        );
    }

    /** @test */
    public function it_can_list_all_the_coupled_permissions_both_directly_and_via_groups()
    {
        $this->testUser->givePermissionTo('edit-news');

        $this->testUserGroup->givePermissionTo('edit-articles');
        $this->testUser->assignGroup('testGroup');

        $this->assertEquals(
            collect(['edit-articles', 'edit-news']),
            $this->testUser->getAllPermissions()->pluck('name')
        );
    }
}
