<?php

namespace Spatie\Permission\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Tests\TestModels\Manager;

class MultipleGuardsTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.guards', [
            'web' => ['driver' => 'session', 'provider' => 'users'],
            'api' => ['driver' => 'token', 'provider' => 'users'],
            'jwt' => ['driver' => 'token', 'provider' => 'users'],
            'abc' => ['driver' => 'abc'],
            'admin' => ['driver' => 'session', 'provider' => 'admins'],
        ]);

        $this->setUpRoutes();
    }

    /**
     * Create routes to test authentication with guards.
     */
    public function setUpRoutes(): void
    {
        Route::middleware('auth:api')->get('/check-api-guard-permission', function (Request $request) {
            return [
                'status' => $request->user()->checkPermissionTo('use_api_guard'),
            ];
        });
    }

    /** @test */
    #[Test]
    public function it_can_give_a_permission_to_a_model_that_is_used_by_multiple_guards()
    {
        $this->testUser->givePermissionTo(app(Permission::class)::create([
            'name' => 'do_this',
            'guard_name' => 'web',
        ]));

        $this->testUser->givePermissionTo(app(Permission::class)::create([
            'name' => 'do_that',
            'guard_name' => 'api',
        ]));

        $this->assertTrue($this->testUser->checkPermissionTo('do_this', 'web'));
        $this->assertTrue($this->testUser->checkPermissionTo('do_that', 'api'));
        $this->assertFalse($this->testUser->checkPermissionTo('do_that', 'web'));
    }

    /** @test */
    #[Test]
    public function the_gate_can_grant_permission_to_a_user_by_passing_a_guard_name()
    {
        $this->testUser->givePermissionTo(app(Permission::class)::create([
            'name' => 'do_this',
            'guard_name' => 'web',
        ]));

        $this->testUser->givePermissionTo(app(Permission::class)::create([
            'name' => 'do_that',
            'guard_name' => 'api',
        ]));

        $this->assertTrue($this->testUser->can('do_this', 'web'));
        $this->assertTrue($this->testUser->can('do_that', 'api'));
        $this->assertFalse($this->testUser->can('do_that', 'web'));

        $this->assertTrue($this->testUser->cannot('do_that', 'web'));
        $this->assertTrue($this->testUser->canAny(['do_this', 'do_that'], 'web'));

        $this->testAdminRole->givePermissionTo($this->testAdminPermission);
        $this->testAdmin->assignRole($this->testAdminRole);

        $this->assertTrue($this->testAdmin->hasPermissionTo($this->testAdminPermission));
        $this->assertTrue($this->testAdmin->can('admin-permission'));
        $this->assertTrue($this->testAdmin->can('admin-permission', 'admin'));
        $this->assertTrue($this->testAdmin->cannot('admin-permission', 'web'));

        $this->assertTrue($this->testAdmin->cannot('non-existing-permission'));
        $this->assertTrue($this->testAdmin->cannot('non-existing-permission', 'web'));
        $this->assertTrue($this->testAdmin->cannot('non-existing-permission', 'admin'));
        $this->assertTrue($this->testAdmin->cannot(['admin-permission', 'non-existing-permission'], 'web'));

        $this->assertFalse($this->testAdmin->can('edit-articles', 'web'));
        $this->assertFalse($this->testAdmin->can('edit-articles', 'admin'));

        $this->assertTrue($this->testUser->cannot('edit-articles', 'admin'));
        $this->assertTrue($this->testUser->cannot('admin-permission', 'admin'));
        $this->assertTrue($this->testUser->cannot('admin-permission', 'web'));
    }

    /** @test */
    #[Test]
    public function it_can_honour_guardName_function_on_model_for_overriding_guard_name_property()
    {
        $user = Manager::create(['email' => 'manager@test.com']);
        $user->givePermissionTo(app(Permission::class)::create([
            'name' => 'do_jwt',
            'guard_name' => 'jwt',
        ]));

        // Manager test user has the guardName override method, which returns 'jwt'
        $this->assertTrue($user->checkPermissionTo('do_jwt', 'jwt'));
        $this->assertTrue($user->hasPermissionTo('do_jwt', 'jwt'));

        // Manager test user has the $guard_name property set to 'web'
        $this->assertFalse($user->checkPermissionTo('do_jwt', 'web'));
    }
}
