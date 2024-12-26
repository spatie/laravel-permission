<?php

namespace Spatie\Permission\Tests;

use Spatie\Permission\Models\Permission;

class WildcardRoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        app('config')->set('permission.enable_wildcard_permission', true);

        Permission::create(['name' => 'other-permission']);

        Permission::create(['name' => 'wrong-guard-permission', 'guard_name' => 'admin']);
    }

    /** @test */
    public function it_can_be_given_a_permission()
    {
        Permission::create(['name' => 'posts.*']);
        $this->testUserRole->givePermissionTo('posts.*');

        $this->assertTrue($this->testUserRole->hasPermissionTo('posts.create'));
    }

    /** @test */
    public function it_can_be_given_multiple_permissions_using_an_array()
    {
        Permission::create(['name' => 'posts.*']);
        Permission::create(['name' => 'news.*']);

        $this->testUserRole->givePermissionTo(['posts.*', 'news.*']);

        $this->assertTrue($this->testUserRole->hasPermissionTo('posts.create'));
        $this->assertTrue($this->testUserRole->hasPermissionTo('news.create'));
    }

    /** @test */
    public function it_can_be_given_multiple_permissions_using_multiple_arguments()
    {
        Permission::create(['name' => 'posts.*']);
        Permission::create(['name' => 'news.*']);

        $this->testUserRole->givePermissionTo('posts.*', 'news.*');

        $this->assertTrue($this->testUserRole->hasPermissionTo('posts.edit.123'));
        $this->assertTrue($this->testUserRole->hasPermissionTo('news.view.1'));
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
    public function it_returns_false_if_permission_does_not_exists()
    {
        $this->assertFalse($this->testUserRole->hasPermissionTo('doesnt-exist'));
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
    public function it_returns_false_when_a_permission_of_the_wrong_guard_is_passed_in()
    {
        $permission = app(Permission::class)->findByName('wrong-guard-permission', 'admin');

        $this->assertFalse($this->testUserRole->hasPermissionTo($permission));
    }
}
