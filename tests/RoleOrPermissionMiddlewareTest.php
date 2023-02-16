<?php

namespace Spatie\Permission\Tests;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\RoleOrPermissionMiddleware;

beforeEach(function () {
    $this->roleOrPermissionMiddleware = new RoleOrPermissionMiddleware();
});

it('a guest cannot access a route protected by the role or permission middleware', function () {
    expect(runMiddleware($this->roleOrPermissionMiddleware, 'testRole'))->toEqual(403);
});

it('a user can access a route protected by permission or role middleware if has this permission or role', function () {
    Auth::login($this->testUser);

    $this->testUser->assignRole('testRole');
    $this->testUser->givePermissionTo('edit-articles');

    expect(runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-news|edit-articles'))->toEqual(200);

    $this->testUser->removeRole('testRole');

    expect(runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles'))->toEqual(200);

    $this->testUser->revokePermissionTo('edit-articles');
    $this->testUser->assignRole('testRole');

    expect(runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles'))->toEqual(200)
        ->and(runMiddleware($this->roleOrPermissionMiddleware, ['testRole', 'edit-articles']))->toEqual(200);
});

it('a user can not access a route protected by permission or role middleware if have not this permission and role', function () {
    Auth::login($this->testUser);

    expect(runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles'))->toEqual(403)
        ->and(runMiddleware($this->roleOrPermissionMiddleware, 'missingRole|missingPermission'))->toEqual(403);

});

it('use not existing custom guard in role or permission', function () {
    $class = null;

    try {
        $this->roleOrPermissionMiddleware->handle(new Request(), function () {
        return (new Response())->setContent('<html></html>');
        }, 'testRole', 'xxx');
    } catch (InvalidArgumentException $e) {
        $class = get_class($e);
    }

    expect($class)->toEqual(InvalidArgumentException::class);
});

it('user can not access permission or role with guard admin while login using default guard', function () {
    Auth::login($this->testUser);

    $this->testUser->assignRole('testRole');
    $this->testUser->givePermissionTo('edit-articles');

    expect(runMiddleware($this->roleOrPermissionMiddleware, 'edit-articles|testRole', 'admin'))->toEqual(403);
});

it('user can access permission or role with guard admin while login using admin guard', function () {
    Auth::guard('admin')->login($this->testAdmin);

    $this->testAdmin->assignRole('testAdminRole');
    $this->testAdmin->givePermissionTo('admin-permission');

    expect(runMiddleware($this->roleOrPermissionMiddleware, 'admin-permission|testAdminRole', 'admin'))->toEqual(200);
});

it('the required permissions or roles can be fetched from the exception', function () {
    Auth::login($this->testUser);

    $message = null;
    $requiredRolesOrPermissions = [];

    try {
        $this->roleOrPermissionMiddleware->handle(new Request(), function () {
        return (new Response())->setContent('<html></html>');
        }, 'some-permission|some-role');
    } catch (UnauthorizedException $e) {
        $message = $e->getMessage();
        $requiredRolesOrPermissions = $e->getRequiredPermissions();
    }

    expect($message)->toEqual('User does not have any of the necessary access rights.')
        ->and($requiredRolesOrPermissions)->toEqual(['some-permission', 'some-role']);
});

it('the required permissions or roles can be displayed in the exception', function () {
    Auth::login($this->testUser);
    Config::set(['permission.display_permission_in_exception' => true]);
    Config::set(['permission.display_role_in_exception' => true]);

    $message = null;

    try {
        $this->roleOrPermissionMiddleware->handle(new Request(), function () {
        return (new Response())->setContent('<html></html>');
        }, 'some-permission|some-role');
    } catch (UnauthorizedException $e) {
        $message = $e->getMessage();
    }

    expect($message)->toEndWith('Necessary roles or permissions are some-permission, some-role');
});
