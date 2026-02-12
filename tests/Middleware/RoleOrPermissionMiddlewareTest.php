<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Spatie\Permission\Tests\TestSupport\TestModels\UserWithoutHasRoles;

beforeEach(function () {
    $this->setUpPassport();
    $this->roleOrPermissionMiddleware = new RoleOrPermissionMiddleware;
});

it('a guest cannot access a route protected by the role or permission middleware', function () {
    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole'))->toEqual(403);
});

it('a user can access a route protected by permission or role middleware if has this permission or role', function () {
    Auth::login($this->testUser);

    $this->testUser->assignRole('testRole');
    $this->testUser->givePermissionTo('edit-articles');

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-news|edit-articles'))->toEqual(200);

    $this->testUser->removeRole('testRole');

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles'))->toEqual(200);

    $this->testUser->revokePermissionTo('edit-articles');
    $this->testUser->assignRole('testRole');

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles'))->toEqual(200);
    expect($this->runMiddleware($this->roleOrPermissionMiddleware, ['testRole', 'edit-articles']))->toEqual(200);
});

it('a client can access a route protected by permission or role middleware if has this permission or role', function () {
    Passport::actingAsClient($this->testClient, ['*']);

    $this->testClient->assignRole('clientRole');
    $this->testClient->givePermissionTo('edit-posts');

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'clientRole|edit-news|edit-posts', null, true))->toEqual(200);

    $this->testClient->removeRole('clientRole');

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'clientRole|edit-posts', null, true))->toEqual(200);

    $this->testClient->revokePermissionTo('edit-posts');
    $this->testClient->assignRole('clientRole');

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'clientRole|edit-posts', null, true))->toEqual(200);
    expect($this->runMiddleware($this->roleOrPermissionMiddleware, ['clientRole', 'edit-posts'], null, true))->toEqual(200);
});

it('a super admin user can access a route protected by permission or role middleware', function () {
    Auth::login($this->testUser);

    Gate::before(function ($user, $ability) {
        return $user->getKey() === $this->testUser->getKey() ? true : null;
    });

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles'))->toEqual(200);
});

it('a user can not access a route protected by permission or role middleware if have not has roles trait', function () {
    $userWithoutHasRoles = UserWithoutHasRoles::create(['email' => 'test_not_has_roles@user.com']);

    Auth::login($userWithoutHasRoles);

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles'))->toEqual(403);
});

it('a user can not access a route protected by permission or role middleware if have not this permission and role', function () {
    Auth::login($this->testUser);

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles'))->toEqual(403);
    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'missingRole|missingPermission'))->toEqual(403);
});

it('a client can not access a route protected by permission or role middleware if have not this permission and role', function () {
    Passport::actingAsClient($this->testClient, ['*']);

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'clientRole|edit-posts', null, true))->toEqual(403);
    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'missingRole|missingPermission', null, true))->toEqual(403);
});

it('use not existing custom guard in role or permission', function () {
    $class = null;

    try {
        $this->roleOrPermissionMiddleware->handle(new Request, function () {
            return (new Response)->setContent('<html></html>');
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

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'edit-articles|testRole', 'admin'))->toEqual(403);
});

it('client can not access permission or role with guard admin while login using default guard', function () {
    Passport::actingAsClient($this->testClient, ['*']);

    $this->testClient->assignRole('clientRole');
    $this->testClient->givePermissionTo('edit-posts');

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'edit-posts|clientRole', 'admin', true))->toEqual(403);
});

it('user can access permission or role with guard admin while login using admin guard', function () {
    Auth::guard('admin')->login($this->testAdmin);

    $this->testAdmin->assignRole('testAdminRole');
    $this->testAdmin->givePermissionTo('admin-permission');

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'admin-permission|testAdminRole', 'admin'))->toEqual(200);
});

it('the required permissions or roles can be fetched from the exception', function () {
    Auth::login($this->testUser);

    $message = null;
    $requiredRolesOrPermissions = [];

    try {
        $this->roleOrPermissionMiddleware->handle(new Request, function () {
            return (new Response)->setContent('<html></html>');
        }, 'some-permission|some-role');
    } catch (UnauthorizedException $e) {
        $message = $e->getMessage();
        $requiredRolesOrPermissions = $e->getRequiredPermissions();
    }

    expect($message)->toEqual('User does not have any of the necessary access rights.');
    expect($requiredRolesOrPermissions)->toEqual(['some-permission', 'some-role']);
});

it('the required permissions or roles can be displayed in the exception', function () {
    Auth::login($this->testUser);
    Config::set(['permission.display_permission_in_exception' => true]);
    Config::set(['permission.display_role_in_exception' => true]);

    $message = null;

    try {
        $this->roleOrPermissionMiddleware->handle(new Request, function () {
            return (new Response)->setContent('<html></html>');
        }, 'some-permission|some-role');
    } catch (UnauthorizedException $e) {
        $message = $e->getMessage();
    }

    expect($message)->toEndWith('Necessary roles or permissions are some-permission, some-role');
});

it('the middleware can be created with static using method', function () {
    expect(RoleOrPermissionMiddleware::using('edit-articles'))
        ->toBe('Spatie\Permission\Middleware\RoleOrPermissionMiddleware:edit-articles');

    expect(RoleOrPermissionMiddleware::using('edit-articles', 'my-guard'))
        ->toEqual('Spatie\Permission\Middleware\RoleOrPermissionMiddleware:edit-articles,my-guard');

    expect(RoleOrPermissionMiddleware::using(['edit-articles', 'testAdminRole']))
        ->toEqual('Spatie\Permission\Middleware\RoleOrPermissionMiddleware:edit-articles|testAdminRole');
});
