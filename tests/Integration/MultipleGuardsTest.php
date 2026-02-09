<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Tests\TestSupport\TestModels\Manager;

beforeEach(function () {
    app('config')->set('auth.guards', [
        'web' => ['driver' => 'session', 'provider' => 'users'],
        'api' => ['driver' => 'token', 'provider' => 'users'],
        'jwt' => ['driver' => 'token', 'provider' => 'users'],
        'abc' => ['driver' => 'abc'],
        'admin' => ['driver' => 'session', 'provider' => 'admins'],
    ]);

    Route::middleware('auth:api')->get('/check-api-guard-permission', function (Request $request) {
        return ['status' => $request->user()->checkPermissionTo('use_api_guard')];
    });
});

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

it('the gate can grant permission to a user by passing a guard name', function () {
    $this->testUser->givePermissionTo(app(Permission::class)::create([
        'name' => 'do_this',
        'guard_name' => 'web',
    ]));

    $this->testUser->givePermissionTo(app(Permission::class)::create([
        'name' => 'do_that',
        'guard_name' => 'api',
    ]));

    expect($this->testUser->can('do_this', 'web'))->toBeTrue();
    expect($this->testUser->can('do_that', 'api'))->toBeTrue();
    expect($this->testUser->can('do_that', 'web'))->toBeFalse();

    expect($this->testUser->cannot('do_that', 'web'))->toBeTrue();
    expect($this->testUser->canAny(['do_this', 'do_that'], 'web'))->toBeTrue();

    $this->testAdminRole->givePermissionTo($this->testAdminPermission);
    $this->testAdmin->assignRole($this->testAdminRole);

    expect($this->testAdmin->hasPermissionTo($this->testAdminPermission))->toBeTrue();
    expect($this->testAdmin->can('admin-permission'))->toBeTrue();
    expect($this->testAdmin->can('admin-permission', 'admin'))->toBeTrue();
    expect($this->testAdmin->cannot('admin-permission', 'web'))->toBeTrue();

    expect($this->testAdmin->cannot('non-existing-permission'))->toBeTrue();
    expect($this->testAdmin->cannot('non-existing-permission', 'web'))->toBeTrue();
    expect($this->testAdmin->cannot('non-existing-permission', 'admin'))->toBeTrue();
    expect($this->testAdmin->cannot(['admin-permission', 'non-existing-permission'], 'web'))->toBeTrue();

    expect($this->testAdmin->can('edit-articles', 'web'))->toBeFalse();
    expect($this->testAdmin->can('edit-articles', 'admin'))->toBeFalse();

    expect($this->testUser->cannot('edit-articles', 'admin'))->toBeTrue();
    expect($this->testUser->cannot('admin-permission', 'admin'))->toBeTrue();
    expect($this->testUser->cannot('admin-permission', 'web'))->toBeTrue();
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
