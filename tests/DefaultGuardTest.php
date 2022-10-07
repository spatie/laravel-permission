<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Models\Role;

class DefaultGuardTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.guards', [
            'web' => ['driver' => 'session', 'provider' => 'users'],
            'api' => ['driver' => 'token', 'provider' => 'users'],
            'jwt' => ['driver' => 'token', 'provider' => 'users'],
        ]);
    }

    /** @test */
    public function it_checks_against_first_matching_guard_name_by_default()
    {
        $this->testUser->givePermissionTo(app(Permission::class)::create([
            'name' => 'do_this',
            'guard_name' => 'web',
        ]));

        $this->testUser->givePermissionTo(app(Permission::class)::create([
            'name' => 'do_that',
            'guard_name' => 'api',
        ]));

        $permission = app(Permission::class)::create([
            'name' => 'do_that',
            'guard_name' => 'web',
        ]);

        $this->assertTrue($this->testUser->checkPermissionTo('do_this'));
        $this->assertFalse($this->testUser->checkPermissionTo('do_that'));
        $this->assertFalse($this->testUser->checkPermissionTo('do_that', 'web'));
        $this->assertTrue($this->testUser->checkPermissionTo('do_that', 'api'));

        $this->testUser->givePermissionTo($permission);

        $this->assertTrue($this->testUser->checkPermissionTo('do_that'));
    }

    /** @test */
    public function it_checks_against_default_guard_if_configured()
    {
        config(['permission.default_guard' => 'api']);

        $this->testUser->givePermissionTo(app(Permission::class)::create([
            'name' => 'do_this',
            'guard_name' => 'web',
        ]));

        $apiPermission = app(Permission::class)::create([
            'name' => 'do_that',
            'guard_name' => 'api',
        ]);

        $this->testUser->givePermissionTo($apiPermission);

        $permission = app(Permission::class)::create([
            'name' => 'do_that',
            'guard_name' => 'web',
        ]);

        $this->assertFalse($this->testUser->checkPermissionTo('do_this'));
        $this->assertTrue($this->testUser->checkPermissionTo('do_that'));
        $this->assertFalse($this->testUser->checkPermissionTo('do_that', 'web'));
        $this->assertTrue($this->testUser->checkPermissionTo('do_that', 'api'));

        $this->testUser->revokePermissionTo($apiPermission);
        $this->testUser->givePermissionTo($permission);

        $this->assertFalse($this->testUser->checkPermissionTo('do_that'));
        $this->assertTrue($this->testUser->checkPermissionTo('do_that', 'web'));
    }

    /** @test */
    public function it_uses_auth_default_guard_if_configured()
    {
        config(['auth.defaults.guard' => 'api']);
        config(['permission.default_guard' => true]);

        $this->testUser->givePermissionTo(app(Permission::class)::create([
            'name' => 'do_this',
            'guard_name' => 'web',
        ]));

        $apiPermission = app(Permission::class)::create([
            'name' => 'do_that',
            'guard_name' => 'api',
        ]);

        $this->testUser->givePermissionTo($apiPermission);

        $permission = app(Permission::class)::create([
            'name' => 'do_that',
            'guard_name' => 'web',
        ]);

        $this->assertFalse($this->testUser->checkPermissionTo('do_this'));
        $this->assertTrue($this->testUser->checkPermissionTo('do_that'));
        $this->assertFalse($this->testUser->checkPermissionTo('do_that', 'web'));
        $this->assertTrue($this->testUser->checkPermissionTo('do_that', 'api'));
    }

    /** @test */
    public function it_creates_roles_for_auth_default_guard_if_configured()
    {
        $this->assertEquals('web', Role::create(['name' => 'admin'])->guard_name);

        config(['auth.defaults.guard' => 'api']);
        config(['permission.default_guard' => true]);

        $this->assertEquals('api', Role::create(['name' => 'admin'])->guard_name);
        $this->assertEquals('jwt', Role::create(['name' => 'admin', 'guard_name' => 'jwt'])->guard_name);

        config(['permission.default_guard' => 'web']);

        $this->assertEquals('api', Role::create(['name' => 'supa doopa admin'])->guard_name);
    }
}
