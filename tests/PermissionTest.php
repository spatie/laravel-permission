<?php

namespace Spatie\Permission\Tests;

use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Tests\TestModels\User;

class PermissionTest extends TestCase
{
    /** @test */
    public function it_get_user_models_using_with()
    {
        $this->testUser->givePermissionTo($this->testUserPermission);

        $permission = app(Permission::class)::with('users')
            ->where($this->testUserPermission->getKeyName(), $this->testUserPermission->getKey())
            ->first();

        $this->assertEquals($permission->getKey(), $this->testUserPermission->getKey());
        $this->assertCount(1, $permission->users);
        $this->assertEquals($permission->users[0]->id, $this->testUser->id);
    }

    /** @test */
    public function it_throws_an_exception_when_the_permission_already_exists()
    {
        $this->expectException(PermissionAlreadyExists::class);

        app(Permission::class)->create(['name' => 'test-permission']);
        app(Permission::class)->create(['name' => 'test-permission']);
    }

    /** @test */
    public function it_belongs_to_a_guard()
    {
        $permission = app(Permission::class)->create(['name' => 'can-edit', 'guard_name' => 'admin']);

        $this->assertEquals('admin', $permission->guard_name);
    }

    /** @test */
    public function it_belongs_to_the_default_guard_by_default()
    {
        $this->assertEquals(
            $this->app['config']->get('auth.defaults.guard'),
            $this->testUserPermission->guard_name
        );
    }

    /** @test */
    public function it_has_user_models_of_the_right_class()
    {
        $this->testAdmin->givePermissionTo($this->testAdminPermission);

        $this->testUser->givePermissionTo($this->testUserPermission);

        $this->assertCount(1, $this->testUserPermission->users);
        $this->assertTrue($this->testUserPermission->users->first()->is($this->testUser));
        $this->assertInstanceOf(User::class, $this->testUserPermission->users->first());
    }

    /** @test */
    public function it_is_retrievable_by_id()
    {
        $permission_by_id = app(Permission::class)->findById($this->testUserPermission->id);

        $this->assertEquals($this->testUserPermission->id, $permission_by_id->id);
    }
}
