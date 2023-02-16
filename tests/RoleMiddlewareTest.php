<?php

namespace Spatie\Permission\Tests;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\RoleMiddleware;

beforeEach(function () {
    $this->roleMiddleware = new RoleMiddleware();
});

it('a guest cannot access a route protected by rolemiddleware', function () {
    expect(runMiddleware($this->roleMiddleware, 'testRole'))->toEqual(403);
});

it('a user cannot access a route protected by role middleware of another guard', function () {
    Auth::login($this->testUser);

    $this->testUser->assignRole('testRole');

    expect(runMiddleware($this->roleMiddleware, 'testAdminRole'))->toEqual(403);
});

it('a user can access a route protected by role middleware if have this role', function () {
    Auth::login($this->testUser);

    $this->testUser->assignRole('testRole');

    expect(runMiddleware($this->roleMiddleware, 'testRole'))->toEqual(200);
});

it('a user can access a route protected by this role middleware if have one of the roles', function () {
    Auth::login($this->testUser);

    $this->testUser->assignRole('testRole');

    expect(runMiddleware($this->roleMiddleware, 'testRole|testRole2'))->toEqual(200);

    expect(runMiddleware($this->roleMiddleware, ['testRole2', 'testRole']))->toEqual(200);
});

it('a user cannot access a route protected by the role middleware if have a different role', function () {
    Auth::login($this->testUser);

    $this->testUser->assignRole(['testRole']);

    expect(runMiddleware($this->roleMiddleware, 'testRole2'))->toEqual(403);
});

it('a user cannot access a route protected by role middleware if have not roles', function () {
    Auth::login($this->testUser);

    expect(runMiddleware($this->roleMiddleware, 'testRole|testRole2'))->toEqual(403);
});

it('a user cannot access a route protected by role middleware if role is undefined', function () {
    Auth::login($this->testUser);

    expect(runMiddleware($this->roleMiddleware, ''))->toEqual(403);
});

it('the required roles can be fetched from the exception', function () {
    Auth::login($this->testUser);

    $message = null;
    $requiredRoles = [];

    try {
        $this->roleMiddleware->handle(new Request(), function () {
        return (new Response())->setContent('<html></html>');
        }, 'some-role');
    } catch (UnauthorizedException $e) {
        $message = $e->getMessage();
        $requiredRoles = $e->getRequiredRoles();
    }

    expect($message)->toEqual('User does not have the right roles.');
    expect($requiredRoles)->toEqual(['some-role']);
});

it('the required roles can be displayed in the exception', function () {
    Auth::login($this->testUser);
    Config::set(['permission.display_role_in_exception' => true]);

    $message = null;

    try {
        $this->roleMiddleware->handle(new Request(), function () {
        return (new Response())->setContent('<html></html>');
        }, 'some-role');
    } catch (UnauthorizedException $e) {
        $message = $e->getMessage();
    }

    expect($message)->toEndWith('Necessary roles are some-role');
});

it('use not existing custom guard in role', function () {
    $class = null;

    try {
        $this->roleMiddleware->handle(new Request(), function () {
        return (new Response())->setContent('<html></html>');
        }, 'testRole', 'xxx');
    } catch (InvalidArgumentException $e) {
        $class = get_class($e);
    }

    expect($class)->toEqual(InvalidArgumentException::class);
});

it('user can not access role with guard admin while login using default guard', function () {
    Auth::login($this->testUser);

    $this->testUser->assignRole('testRole');

    expect(runMiddleware($this->roleMiddleware, 'testRole', 'admin'))->toEqual(403);
});

it('user can access role with guard admin while login using admin guard', function () {
    Auth::guard('admin')->login($this->testAdmin);

    $this->testAdmin->assignRole('testAdminRole');

    expect(runMiddleware($this->roleMiddleware, 'testAdminRole', 'admin'))->toEqual(200);
});
