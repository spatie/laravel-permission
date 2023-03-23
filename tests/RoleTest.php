<?php

namespace Spatie\Permission\Tests;

use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestModels\Admin;
use Spatie\Permission\Tests\TestModels\RuntimeRole;
use Spatie\Permission\Tests\TestModels\User;

class RoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'other-permission']);

        Permission::create(['name' => 'wrong-guard-permission', 'guard_name' => 'admin']);
    }

    /** @test */
    public function it_get_user_models_using_with()
    {
        $this->testUser->assignRole($this->testUserRole);

        $role = app(Role::class)::with('users')
            ->where($this->testUserRole->getKeyName(), $this->testUserRole->getKey())->first();

        $this->assertEquals($role->getKey(), $this->testUserRole->getKey());
        $this->assertCount(1, $role->users);
        $this->assertEquals($role->users[0]->id, $this->testUser->id);
    }

    /** @test */
    public function it_has_user_models_of_the_right_class()
    {
        $this->testAdmin->assignRole($this->testAdminRole);

        $this->testUser->assignRole($this->testUserRole);

        $this->assertCount(1, $this->testUserRole->users);
        $this->assertTrue($this->testUserRole->users->first()->is($this->testUser));
        $this->assertInstanceOf(User::class, $this->testUserRole->users->first());

        $this->assertCount(1, $this->testAdminRole->users);
        $this->assertTrue($this->testAdminRole->users->first()->is($this->testAdmin));
        $this->assertInstanceOf(Admin::class, $this->testAdminRole->users->first());
    }

    /** @test */
    public function it_throws_an_exception_when_the_role_already_exists()
    {
        $this->expectException(RoleAlreadyExists::class);

        app(Role::class)->create(['name' => 'test-role']);
        app(Role::class)->create(['name' => 'test-role']);
    }

    /** @test */
    public function it_can_be_given_a_permission()
    {
        $this->testUserRole->givePermissionTo('edit-articles');

        $this->assertTrue($this->testUserRole->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_throws_an_exception_when_given_a_permission_that_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUserRole->givePermissionTo('create-evil-empire');
    }

    /** @test */
    public function it_throws_an_exception_when_given_a_permission_that_belongs_to_another_guard()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUserRole->givePermissionTo('admin-permission');

        $this->expectException(GuardDoesNotMatch::class);

        $this->testUserRole->givePermissionTo($this->testAdminPermission);
    }

    /** @test */
    public function it_can_be_given_multiple_permissions_using_an_array()
    {
        $this->testUserRole->givePermissionTo(['edit-articles', 'edit-news']);

        $this->assertTrue($this->testUserRole->hasPermissionTo('edit-articles'));
        $this->assertTrue($this->testUserRole->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function it_can_be_given_multiple_permissions_using_multiple_arguments()
    {
        $this->testUserRole->givePermissionTo('edit-articles', 'edit-news');

        $this->assertTrue($this->testUserRole->hasPermissionTo('edit-articles'));
        $this->assertTrue($this->testUserRole->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function it_can_sync_permissions()
    {
        $this->testUserRole->givePermissionTo('edit-articles');

        $this->testUserRole->syncPermissions('edit-news');

        $this->assertFalse($this->testUserRole->hasPermissionTo('edit-articles'));

        $this->assertTrue($this->testUserRole->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function it_throws_an_exception_when_syncing_permissions_that_do_not_exist()
    {
        $this->testUserRole->givePermissionTo('edit-articles');

        $this->expectException(PermissionDoesNotExist::class);

        $this->testUserRole->syncPermissions('permission-does-not-exist');
    }

    /** @test */
    public function it_throws_an_exception_when_syncing_permissions_that_belong_to_a_different_guard()
    {
        $this->testUserRole->givePermissionTo('edit-articles');

        $this->expectException(PermissionDoesNotExist::class);

        $this->testUserRole->syncPermissions('admin-permission');

        $this->expectException(GuardDoesNotMatch::class);

        $this->testUserRole->syncPermissions($this->testAdminPermission);
    }

    /** @test */
    public function it_will_remove_all_permissions_when_passing_an_empty_array_to_sync_permissions()
    {
        $this->testUserRole->givePermissionTo('edit-articles');

        $this->testUserRole->givePermissionTo('edit-news');

        $this->testUserRole->syncPermissions([]);

        $this->assertFalse($this->testUserRole->hasPermissionTo('edit-articles'));

        $this->assertFalse($this->testUserRole->hasPermissionTo('edit-news'));
    }

    /** @test */
    public function sync_permission_error_does_not_detach_permissions()
    {
        $this->testUserRole->givePermissionTo('edit-news');

        $this->expectException(PermissionDoesNotExist::class);

        $this->testUserRole->syncPermissions('edit-articles', 'permission-that-does-not-exist');

        $this->assertTrue($this->testUserRole->fresh()->hasDirectPermission('edit-news'));
    }

    /** @test */
    public function it_can_revoke_a_permission()
    {
        $this->testUserRole->givePermissionTo('edit-articles');

        $this->assertTrue($this->testUserRole->hasPermissionTo('edit-articles'));

        $this->testUserRole->revokePermissionTo('edit-articles');

        $this->testUserRole = $this->testUserRole->fresh();

        $this->assertFalse($this->testUserRole->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_can_be_given_a_permission_using_objects()
    {
        $this->testUserRole->givePermissionTo($this->testUserPermission);

        $this->assertTrue($this->testUserRole->hasPermissionTo($this->testUserPermission));
    }

    /** @test */
    public function it_returns_false_if_it_does_not_have_the_permission()
    {
        $this->assertFalse($this->testUserRole->hasPermissionTo('other-permission'));
    }

    /** @test */
    public function it_throws_an_exception_if_the_permission_does_not_exist()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUserRole->hasPermissionTo('doesnt-exist');
    }

    /** @test */
    public function it_returns_false_if_it_does_not_have_a_permission_object()
    {
        $permission = app(Permission::class)->findByName('other-permission');

        $this->assertFalse($this->testUserRole->hasPermissionTo($permission));
    }

    /** @test */
    public function it_creates_permission_object_with_findOrCreate_if_it_does_not_have_a_permission_object()
    {
        $permission = app(Permission::class)->findOrCreate('another-permission');

        $this->assertFalse($this->testUserRole->hasPermissionTo($permission));

        $this->testUserRole->givePermissionTo($permission);

        $this->testUserRole = $this->testUserRole->fresh();

        $this->assertTrue($this->testUserRole->hasPermissionTo('another-permission'));
    }

    /** @test */
    public function it_creates_a_role_with_findOrCreate_if_the_named_role_does_not_exist()
    {
        $this->expectException(RoleDoesNotExist::class);

        $role1 = app(Role::class)->findByName('non-existing-role');

        $this->assertNull($role1);

        $role2 = app(Role::class)->findOrCreate('yet-another-role');

        $this->assertInstanceOf(Role::class, $role2);
    }

    /** @test */
    public function it_throws_an_exception_when_a_permission_of_the_wrong_guard_is_passed_in()
    {
        $this->expectException(GuardDoesNotMatch::class);

        $permission = app(Permission::class)->findByName('wrong-guard-permission', 'admin');

        $this->testUserRole->hasPermissionTo($permission);
    }

    /** @test */
    public function it_belongs_to_a_guard()
    {
        $role = app(Role::class)->create(['name' => 'admin', 'guard_name' => 'admin']);

        $this->assertEquals('admin', $role->guard_name);
    }

    /** @test */
    public function it_belongs_to_the_default_guard_by_default()
    {
        $this->assertEquals(
            $this->app['config']->get('auth.defaults.guard'),
            $this->testUserRole->guard_name
        );
    }

    /** @test */
    public function it_can_change_role_class_on_runtime()
    {
        $role = app(Role::class)->create(['name' => 'test-role-old']);
        $this->assertNotInstanceOf(RuntimeRole::class, $role);

        $role->givePermissionTo('edit-articles');

        app('config')->set('permission.models.role', RuntimeRole::class);
        app()->bind(Role::class, RuntimeRole::class);
        app(PermissionRegistrar::class)->setRoleClass(RuntimeRole::class);

        $permission = app(Permission::class)->findByName('edit-articles');
        $this->assertInstanceOf(RuntimeRole::class, $permission->roles[0]);
        $this->assertSame('test-role-old', $permission->roles[0]->name);

        $role = app(Role::class)->create(['name' => 'test-role']);
        $this->assertInstanceOf(RuntimeRole::class, $role);

        $this->testUser->assignRole('test-role');
        $this->assertTrue($this->testUser->hasRole('test-role'));
        $this->assertInstanceOf(RuntimeRole::class, $this->testUser->roles[0]);
        $this->assertSame('test-role', $this->testUser->roles[0]->name);
    }
}
