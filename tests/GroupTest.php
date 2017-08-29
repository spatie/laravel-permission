<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Contracts\Group;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\GroupAlreadyExists;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class GroupTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Permission::create(['name' => 'other-permission']);

        Permission::create(['name' => 'wrong-guard-permission', 'guard_name' => 'admin']);
    }

    /** @test */
    public function it_has_user_models_of_the_right_class()
    {
        $this->testAdmin->assignGroup($this->testAdminGroup);

        $this->testUser->assignGroup($this->testUserGroup);

        $this->assertCount(1, $this->testUserGroup->users);
        $this->assertTrue($this->testUserGroup->users->first()->is($this->testUser));
        $this->assertInstanceOf(User::class, $this->testUserGroup->users->first());
    }

    /** @test */
    public function it_throws_an_exception_when_the_group_already_exists()
    {
        $this->expectException(GroupAlreadyExists::class);

        app(Group::class)->create(['name' => 'test-group']);
        app(Group::class)->create(['name' => 'test-group']);
    }

    /** @test */
    public function it_can_be_given_a_permission()
    {
        $this->testUserGroup->givePermissionTo('edit-articles');

        $this->assertTrue($this->testUserGroup->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_throws_an_exception_when_given_a_permission_that_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUserGroup->givePermissionTo('create-evil-empire');
    }

    /** @test */
    public function it_throws_an_exception_when_given_a_permission_that_belongs_to_another_guard()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUserGroup->givePermissionTo('admin-permission');

        $this->expectException(GuardDoesNotMatch::class);

        $this->testUserGroup->givePermissionTo($this->testAdminPermission);
    }

    /** @test */
    public function it_can_be_given_multiple_permissions_using_an_array()
    {
        $this->testUserGroup->givePermissionTo(['edit-articles', 'edit-news']);

        $this->assertTrue($this->testUserGroup->hasPermissionTo('edit-articles'));
        $this->assertTrue($this->testUserGroup->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function it_can_be_given_multiple_permissions_using_multiple_arguments()
    {
        $this->testUserGroup->givePermissionTo('edit-articles', 'edit-news');

        $this->assertTrue($this->testUserGroup->hasPermissionTo('edit-articles'));
        $this->assertTrue($this->testUserGroup->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function it_can_sync_permissions()
    {
        $this->testUserGroup->givePermissionTo('edit-articles');

        $this->testUserGroup->syncPermissions('edit-news');

        $this->assertFalse($this->testUserGroup->hasPermissionTo('edit-articles'));

        $this->assertTrue($this->testUserGroup->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function it_throws_an_exception_when_syncing_permissions_that_do_not_exist()
    {
        $this->testUserGroup->givePermissionTo('edit-articles');

        $this->expectException(PermissionDoesNotExist::class);

        $this->testUserGroup->syncPermissions('permission-does-not-exist');
    }

    /** @test */
    public function it_throws_an_exception_when_syncing_permissions_that_belong_to_a_different_guard()
    {
        $this->testUserGroup->givePermissionTo('edit-articles');

        $this->expectException(PermissionDoesNotExist::class);

        $this->testUserGroup->syncPermissions('admin-permission');

        $this->expectException(GuardDoesNotMatch::class);

        $this->testUserGroup->syncPermissions($this->testAdminPermission);
    }

    /** @test */
    public function it_will_remove_all_permissions_when_passing_an_empty_array_to_sync_permissions()
    {
        $this->testUserGroup->givePermissionTo('edit-articles');

        $this->testUserGroup->givePermissionTo('edit-news');

        $this->testUserGroup->syncPermissions([]);

        $this->assertFalse($this->testUserGroup->hasPermissionTo('edit-articles'));

        $this->assertFalse($this->testUserGroup->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function it_can_revoked_a_permission()
    {
        $this->testUserGroup->givePermissionTo('edit-articles');

        $this->assertTrue($this->testUserGroup->hasPermissionTo('edit-articles'));

        $this->testUserGroup->revokePermissionTo('edit-articles');

        $this->testUserGroup = $this->testUserGroup->fresh();

        $this->assertFalse($this->testUserGroup->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_can_be_given_a_permission_using_objects()
    {
        $this->testUserGroup->givePermissionTo($this->testUserPermission);

        $this->assertTrue($this->testUserGroup->hasPermissionTo($this->testUserPermission));
    }

    /** @test */
    public function it_returns_false_if_it_does_not_have_the_permission()
    {
        $this->assertFalse($this->testUserGroup->hasPermissionTo('other-permission'));
    }

    /** @test */
    public function it_throws_an_exception_if_the_permission_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUserGroup->hasPermissionTo('doesnt-exist');
    }

    /** @test */
    public function it_returns_false_if_it_does_not_have_a_permission_object()
    {
        $permission = app(Permission::class)->findByName('other-permission');

        $this->assertFalse($this->testUserGroup->hasPermissionTo($permission));
    }

    /** @test */
    public function it_throws_an_exception_when_a_permission_of_the_wrong_guard_is_passed_in()
    {
        $this->expectException(GuardDoesNotMatch::class);

        $permission = app(Permission::class)->findByName('wrong-guard-permission', 'admin');

        $this->testUserGroup->hasPermissionTo($permission);
    }

    /** @test */
    public function it_belongs_to_a_guard()
    {
        $group = app(Group::class)->create(['name' => 'admin', 'guard_name' => 'admin']);

        $this->assertEquals('admin', $group->guard_name);
    }

    /** @test */
    public function it_belongs_to_the_default_guard_by_default()
    {
        $this->assertEquals($this->app['config']->get('auth.defaults.guard'), $this->testUserGroup->guard_name);
    }
}
