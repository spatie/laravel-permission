<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Exceptions\GuardMismatch;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class RoleTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Permission::create(['name' => 'other-permission']);

        Permission::create(['name' => 'wrong-guard-permission', 'guard_name' => 'admin']);

        $this->testUserRole->givePermissionTo($this->testUserPermission);
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
        $this->assertEquals($this->app['config']->get('auth.defaults.guard'), $this->testUserRole->guard_name);
    }

    /** @test */
    public function it_returns_true_if_role_has_permission()
    {
        $this->assertTrue($this->testUserRole->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_returns_false_if_role_has_not_permission()
    {
        $this->assertFalse($this->testUserRole->hasPermissionTo('other-permission'));
    }

    /** @test */
    public function it_throws_an_exception_if_the_permission_is_not_found()
    {
        $this->expectException(PermissionDoesNotExist::class);

        $this->testUserRole->hasPermissionTo('doesnt-exist');
    }

    /** @test */
    public function it_allows_permission_models_to_be_passed_in()
    {
        $permission = app(Permission::class)->findByName('edit-articles');

        $this->assertTrue($this->testUserRole->hasPermissionTo($permission));

        $permission = app(Permission::class)->findByName('other-permission');

        $this->assertFalse($this->testUserRole->hasPermissionTo($permission));
    }

    /** @test */
    public function it_throws_an_exception_when_a_permission_of_the_wrong_guard_is_passed_in()
    {
        $this->expectException(GuardMismatch::class);

        $permission = app(Permission::class)->findByName('wrong-guard-permission', 'admin');

        $this->testUserRole->hasPermissionTo($permission);
    }
}
