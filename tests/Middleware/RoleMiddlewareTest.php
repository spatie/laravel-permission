<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Passport;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Tests\TestSupport\TestModels;
use Spatie\Permission\Tests\TestSupport\TestModels\UserWithoutHasRoles;

beforeEach(function () {
    $this->setUpPassport();
    $this->roleMiddleware = new RoleMiddleware;
});

it('a guest cannot access a route protected by rolemiddleware', function () {
    expect($this->runMiddleware($this->roleMiddleware, 'testRole'))->toEqual(403);
});

it('a user cannot access a route protected by role middleware of another guard', function () {
    Auth::login($this->testUser);

    $this->testUser->assignRole('testRole');

    expect($this->runMiddleware($this->roleMiddleware, 'testAdminRole'))->toEqual(403);
});

it('a client cannot access a route protected by role middleware of another guard', function () {
    Passport::actingAsClient($this->testClient, ['*']);

    $this->testClient->assignRole('clientRole');

    expect($this->runMiddleware($this->roleMiddleware, 'testAdminRole', null, true))->toEqual(403);
});

it('a user can access a route protected by role middleware if have this role', function () {
    Auth::login($this->testUser);

    $this->testUser->assignRole('testRole');

    expect($this->runMiddleware($this->roleMiddleware, 'testRole'))->toEqual(200);
});

it('a client can access a route protected by role middleware if have this role', function () {
    Passport::actingAsClient($this->testClient, ['*']);

    $this->testClient->assignRole('clientRole');

    expect($this->runMiddleware($this->roleMiddleware, 'clientRole', null, true))->toEqual(200);
});

it('a user can access a route protected by this role middleware if have one of the roles', function () {
    Auth::login($this->testUser);

    $this->testUser->assignRole('testRole');

    expect($this->runMiddleware($this->roleMiddleware, 'testRole|testRole2'))->toEqual(200);
    expect($this->runMiddleware($this->roleMiddleware, ['testRole2', 'testRole']))->toEqual(200);
});

it('a client can access a route protected by this role middleware if have one of the roles', function () {
    Passport::actingAsClient($this->testClient, ['*']);

    $this->testClient->assignRole('clientRole');

    expect($this->runMiddleware($this->roleMiddleware, 'clientRole|testRole2', null, true))->toEqual(200);
    expect($this->runMiddleware($this->roleMiddleware, ['testRole2', 'clientRole'], null, true))->toEqual(200);
});

it('a user cannot access a route protected by the role middleware if have not has roles trait', function () {
    $userWithoutHasRoles = UserWithoutHasRoles::create(['email' => 'test_not_has_roles@user.com']);

    Auth::login($userWithoutHasRoles);

    expect($this->runMiddleware($this->roleMiddleware, 'testRole'))->toEqual(403);
});

it('a user cannot access a route protected by the role middleware if have a different role', function () {
    Auth::login($this->testUser);

    $this->testUser->assignRole(['testRole']);

    expect($this->runMiddleware($this->roleMiddleware, 'testRole2'))->toEqual(403);
});

it('a client cannot access a route protected by the role middleware if have a different role', function () {
    Passport::actingAsClient($this->testClient, ['*']);

    $this->testClient->assignRole(['clientRole']);

    expect($this->runMiddleware($this->roleMiddleware, 'clientRole2', null, true))->toEqual(403);
});

it('a user cannot access a route protected by role middleware if have not roles', function () {
    Auth::login($this->testUser);

    expect($this->runMiddleware($this->roleMiddleware, 'testRole|testRole2'))->toEqual(403);
});

it('a client cannot access a route protected by role middleware if have not roles', function () {
    Passport::actingAsClient($this->testClient, ['*']);

    expect($this->runMiddleware($this->roleMiddleware, 'testRole|testRole2', null, true))->toEqual(403);
});

it('a user cannot access a route protected by role middleware if role is undefined', function () {
    Auth::login($this->testUser);

    expect($this->runMiddleware($this->roleMiddleware, ''))->toEqual(403);
});

it('a client cannot access a route protected by role middleware if role is undefined', function () {
    Passport::actingAsClient($this->testClient, ['*']);

    expect($this->runMiddleware($this->roleMiddleware, '', null, true))->toEqual(403);
});

it('the required roles can be fetched from the exception', function () {
    Auth::login($this->testUser);

    $message = null;
    $requiredRoles = [];

    try {
        $this->roleMiddleware->handle(new Request, function () {
            return (new Response)->setContent('<html></html>');
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
        $this->roleMiddleware->handle(new Request, function () {
            return (new Response)->setContent('<html></html>');
        }, 'some-role');
    } catch (UnauthorizedException $e) {
        $message = $e->getMessage();
    }

    expect($message)->toEndWith('Necessary roles are some-role');
});

it('use not existing custom guard in role', function () {
    $class = null;

    try {
        $this->roleMiddleware->handle(new Request, function () {
            return (new Response)->setContent('<html></html>');
        }, 'testRole', 'xxx');
    } catch (InvalidArgumentException $e) {
        $class = get_class($e);
    }

    expect($class)->toEqual(InvalidArgumentException::class);
});

it('user can not access role with guard admin while login using default guard', function () {
    Auth::login($this->testUser);

    $this->testUser->assignRole('testRole');

    expect($this->runMiddleware($this->roleMiddleware, 'testRole', 'admin'))->toEqual(403);
});

it('client can not access role with guard admin while login using default guard', function () {
    Passport::actingAsClient($this->testClient, ['*']);

    $this->testClient->assignRole('clientRole');

    expect($this->runMiddleware($this->roleMiddleware, 'clientRole', 'admin', true))->toEqual(403);
});

it('user can access role with guard admin while login using admin guard', function () {
    Auth::guard('admin')->login($this->testAdmin);

    $this->testAdmin->assignRole('testAdminRole');

    expect($this->runMiddleware($this->roleMiddleware, 'testAdminRole', 'admin'))->toEqual(200);
});

it('the middleware can be created with static using method', function () {
    expect(RoleMiddleware::using('testAdminRole'))
        ->toBe('Spatie\Permission\Middleware\RoleMiddleware:testAdminRole');

    expect(RoleMiddleware::using('testAdminRole', 'my-guard'))
        ->toEqual('Spatie\Permission\Middleware\RoleMiddleware:testAdminRole,my-guard');

    expect(RoleMiddleware::using(['testAdminRole', 'anotherRole']))
        ->toEqual('Spatie\Permission\Middleware\RoleMiddleware:testAdminRole|anotherRole');
});

it('the middleware can handle enum based roles with static using method', function () {
    expect(RoleMiddleware::using(TestModels\TestRolePermissionsEnum::Writer))
        ->toBe('Spatie\Permission\Middleware\RoleMiddleware:writer');

    expect(RoleMiddleware::using(TestModels\TestRolePermissionsEnum::Writer, 'my-guard'))
        ->toEqual('Spatie\Permission\Middleware\RoleMiddleware:writer,my-guard');

    expect(RoleMiddleware::using([TestModels\TestRolePermissionsEnum::Writer, TestModels\TestRolePermissionsEnum::Editor]))
        ->toEqual('Spatie\Permission\Middleware\RoleMiddleware:writer|editor');
});

it('the middleware can handle enum based roles with handle method', function () {
    app(Role::class)->create(['name' => TestModels\TestRolePermissionsEnum::Writer->value]);
    app(Role::class)->create(['name' => TestModels\TestRolePermissionsEnum::Editor->value]);

    Auth::login($this->testUser);
    $this->testUser->assignRole(TestModels\TestRolePermissionsEnum::Writer);

    expect($this->runMiddleware($this->roleMiddleware, TestModels\TestRolePermissionsEnum::Writer))
        ->toEqual(200);

    $this->testUser->assignRole(TestModels\TestRolePermissionsEnum::Editor);

    expect($this->runMiddleware($this->roleMiddleware, [TestModels\TestRolePermissionsEnum::Writer, TestModels\TestRolePermissionsEnum::Editor]))
        ->toEqual(200);
});
