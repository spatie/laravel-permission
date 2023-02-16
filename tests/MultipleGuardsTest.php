<?php

namespace Spatie\Permission\Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Tests\TestModels\Manager;

trait SetupMultipleGuardsTest {
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.guards', [
            'web' => ['driver' => 'session', 'provider' => 'users'],
            'api' => ['driver' => 'token', 'provider' => 'users'],
            'jwt' => ['driver' => 'token', 'provider' => 'users'],
            'abc' => ['driver' => 'abc'],
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
}

uses(SetupMultipleGuardsTest::class);

it('can give a permission to a model that is used by multiple guards', function () {
    $this->testUser->givePermissionTo(app(Permission::class)::create([
        'name' => 'do_this',
        'guard_name' => 'web',
    ]));

    $this->testUser->givePermissionTo(app(Permission::class)::create([
        'name' => 'do_that',
        'guard_name' => 'api',
    ]));

    expect($this->testUser->checkPermissionTo('do_this', 'web'))->toBeTrue();
    expect($this->testUser->checkPermissionTo('do_that', 'api'))->toBeTrue();
    expect($this->testUser->checkPermissionTo('do_that', 'web'))->toBeFalse();
});

it('can honour guardName function on model for overriding guard name property', function () {
    $user = Manager::create(['email' => 'manager@test.com']);
    $user->givePermissionTo(app(Permission::class)::create([
        'name' => 'do_jwt',
        'guard_name' => 'jwt',
    ]));

    // Manager test user has the guardName override method, which returns 'jwt'
    expect($user->checkPermissionTo('do_jwt', 'jwt'))->toBeTrue();
    expect($user->hasPermissionTo('do_jwt', 'jwt'))->toBeTrue();

    // Manager test user has the $guard_name property set to 'web'
    expect($user->checkPermissionTo('do_jwt', 'web'))->toBeFalse();
});
