<?php

namespace Spatie\Permission\Tests;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\PermissionMiddleware;

beforeEach(function () {
    $this->permissionMiddleware = new PermissionMiddleware();
});

it('a guest cannot access a route protected by the permission middleware', function () {
    expect(runMiddleware($this->permissionMiddleware, 'edit-articles'))->toEqual(403);
});

it('a user cannot access a route protected by the permission middleware of a different guard', function () {
    // These permissions are created fresh here in reverse order of guard being applied, so they are not "found first" in the db lookup when matching
    app(Permission::class)->create(['name' => 'admin-permission2', 'guard_name' => 'web']);
    $p1 = app(Permission::class)->create(['name' => 'admin-permission2', 'guard_name' => 'admin']);
    app(Permission::class)->create(['name' => 'edit-articles2', 'guard_name' => 'admin']);
    $p2 = app(Permission::class)->create(['name' => 'edit-articles2', 'guard_name' => 'web']);

    Auth::guard('admin')->login($this->testAdmin);

    $this->testAdmin->givePermissionTo($p1);

    expect(runMiddleware($this->permissionMiddleware, 'admin-permission2', 'admin'))->toEqual(200);

    expect(runMiddleware($this->permissionMiddleware, 'edit-articles2', 'admin'))->toEqual(403);

    Auth::login($this->testUser);

    $this->testUser->givePermissionTo($p2);

    expect(runMiddleware($this->permissionMiddleware, 'edit-articles2', 'web'))->toEqual(200);

    expect(runMiddleware($this->permissionMiddleware, 'admin-permission2', 'web'))->toEqual(403);
});

it('a user can access a route protected by permission middleware if have this permission', function () {
    Auth::login($this->testUser);

    $this->testUser->givePermissionTo('edit-articles');

    expect(runMiddleware($this->permissionMiddleware, 'edit-articles'))->toEqual(200);
});

it('a user can access a route protected by this permission middleware if have one of the permissions', function () {
    Auth::login($this->testUser);

    $this->testUser->givePermissionTo('edit-articles');

    expect(runMiddleware($this->permissionMiddleware, 'edit-news|edit-articles'))->toEqual(200);

    expect(runMiddleware($this->permissionMiddleware, ['edit-news', 'edit-articles']))->toEqual(200);
});

it('a user cannot access a route protected by the permission middleware if have a different permission', function () {
    Auth::login($this->testUser);

    $this->testUser->givePermissionTo('edit-articles');

    expect(runMiddleware($this->permissionMiddleware, 'edit-news'))->toEqual(403);
});

it('a user cannot access a route protected by permission middleware if have not permissions', function () {
    Auth::login($this->testUser);

    expect(runMiddleware($this->permissionMiddleware, 'edit-articles|edit-news'))->toEqual(403);
});

it('a user can access a route protected by permission middleware if has permission via role', function () {
    Auth::login($this->testUser);

    expect(runMiddleware($this->permissionMiddleware, 'edit-articles'))->toEqual(403);

    $this->testUserRole->givePermissionTo('edit-articles');
    $this->testUser->assignRole('testRole');

    expect(runMiddleware($this->permissionMiddleware, 'edit-articles'))->toEqual(200);
});

it('the required permissions can be fetched from the exception', function () {
    Auth::login($this->testUser);

    $message = null;
    $requiredPermissions = [];

    try {
        $this->permissionMiddleware->handle(new Request(), function () {
        return (new Response())->setContent('<html></html>');
        }, 'some-permission');
    } catch (UnauthorizedException $e) {
        $message = $e->getMessage();
        $requiredPermissions = $e->getRequiredPermissions();
    }

    expect($message)->toEqual('User does not have the right permissions.');
    expect($requiredPermissions)->toEqual(['some-permission']);
});

it('the required permissions can be displayed in the exception', function () {
    Auth::login($this->testUser);
    Config::set(['permission.display_permission_in_exception' => true]);

    $message = null;

    try {
        $this->permissionMiddleware->handle(new Request(), function () {
        return (new Response())->setContent('<html></html>');
        }, 'some-permission');
    } catch (UnauthorizedException $e) {
        $message = $e->getMessage();
    }

    expect($message)->toEndWith('Necessary permissions are some-permission');
});

it('use not existing custom guard in permission', function () {
    $class = null;

    try {
        $this->permissionMiddleware->handle(new Request(), function () {
        return (new Response())->setContent('<html></html>');
        }, 'edit-articles', 'xxx');
    } catch (InvalidArgumentException $e) {
        $class = get_class($e);
    }

    expect($class)->toEqual(InvalidArgumentException::class);
});

it('user can not access permission with guard admin while login using default guard', function () {
    Auth::login($this->testUser);

    $this->testUser->givePermissionTo('edit-articles');

    expect(runMiddleware($this->permissionMiddleware, 'edit-articles', 'admin'))->toEqual(403);
});

it('user can access permission with guard admin while login using admin guard', function () {
    Auth::guard('admin')->login($this->testAdmin);

    $this->testAdmin->givePermissionTo('admin-permission');

    expect(runMiddleware($this->permissionMiddleware, 'admin-permission', 'admin'))->toEqual(200);
});
