<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Laravel\Passport\Passport;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Tests\TestModels;
use Spatie\Permission\Tests\TestModels\UserWithoutHasRoles;

uses(Spatie\Permission\Tests\PassportTestCase::class);

beforeEach(function () {
    $this->permissionMiddleware = new PermissionMiddleware;
});

it('a guest cannot access a route protected by the permission middleware', function () {
    expect($this->runMiddleware($this->permissionMiddleware, 'edit-articles'))->toEqual(403);
});

it('a user cannot access a route protected by the permission middleware of a different guard', function () {
    // These permissions are created fresh here in reverse order of guard being applied, so they are not "found first" in the db lookup when matching
    app(Permission::class)->create(['name' => 'admin-permission2', 'guard_name' => 'web']);
    $p1 = app(Permission::class)->create(['name' => 'admin-permission2', 'guard_name' => 'admin']);
    app(Permission::class)->create(['name' => 'edit-articles2', 'guard_name' => 'admin']);
    $p2 = app(Permission::class)->create(['name' => 'edit-articles2', 'guard_name' => 'web']);

    Auth::guard('admin')->login($this->testAdmin);

    $this->testAdmin->givePermissionTo($p1);

    expect($this->runMiddleware($this->permissionMiddleware, 'admin-permission2', 'admin'))->toEqual(200);
    expect($this->runMiddleware($this->permissionMiddleware, 'edit-articles2', 'admin'))->toEqual(403);

    Auth::login($this->testUser);

    $this->testUser->givePermissionTo($p2);

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-articles2', 'web'))->toEqual(200);
    expect($this->runMiddleware($this->permissionMiddleware, 'admin-permission2', 'web'))->toEqual(403);
});

it('a client cannot access a route protected by the permission middleware of a different guard', function () {
    if ($this->getLaravelVersion() < 9) {
        $this->markTestSkipped('requires laravel >= 9');
    }

    // These permissions are created fresh here in reverse order of guard being applied, so they are not "found first" in the db lookup when matching
    app(Permission::class)->create(['name' => 'admin-permission2', 'guard_name' => 'web']);
    $p1 = app(Permission::class)->create(['name' => 'admin-permission2', 'guard_name' => 'api']);

    Passport::actingAsClient($this->testClient, ['*']);

    $this->testClient->givePermissionTo($p1);

    expect($this->runMiddleware($this->permissionMiddleware, 'admin-permission2', 'api', true))->toEqual(200);
    expect($this->runMiddleware($this->permissionMiddleware, 'edit-articles2', 'web', true))->toEqual(403);
});

it('a super admin user can access a route protected by permission middleware', function () {
    Auth::login($this->testUser);

    Gate::before(function ($user, $ability) {
        return $user->getKey() === $this->testUser->getKey() ? true : null;
    });

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-articles'))->toEqual(200);
});

it('a user can access a route protected by permission middleware if have this permission', function () {
    Auth::login($this->testUser);

    $this->testUser->givePermissionTo('edit-articles');

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-articles'))->toEqual(200);
});

it('a client can access a route protected by permission middleware if have this permission', function () {
    if ($this->getLaravelVersion() < 9) {
        $this->markTestSkipped('requires laravel >= 9');
    }

    Passport::actingAsClient($this->testClient, ['*'], 'api');

    $this->testClient->givePermissionTo('edit-posts');

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-posts', null, true))->toEqual(200);
});

it('a user can access a route protected by this permission middleware if have one of the permissions', function () {
    Auth::login($this->testUser);

    $this->testUser->givePermissionTo('edit-articles');

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-news|edit-articles'))->toEqual(200);
    expect($this->runMiddleware($this->permissionMiddleware, ['edit-news', 'edit-articles']))->toEqual(200);
});

it('a client can access a route protected by this permission middleware if have one of the permissions', function () {
    if ($this->getLaravelVersion() < 9) {
        $this->markTestSkipped('requires laravel >= 9');
    }

    Passport::actingAsClient($this->testClient, ['*']);

    $this->testClient->givePermissionTo('edit-posts');

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-news|edit-posts', null, true))->toEqual(200);
    expect($this->runMiddleware($this->permissionMiddleware, ['edit-news', 'edit-posts'], null, true))->toEqual(200);
});

it('a user cannot access a route protected by the permission middleware if have not has roles trait', function () {
    $userWithoutHasRoles = UserWithoutHasRoles::create(['email' => 'test_not_has_roles@user.com']);

    Auth::login($userWithoutHasRoles);

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-news'))->toEqual(403);
});

it('a user cannot access a route protected by the permission middleware if have a different permission', function () {
    Auth::login($this->testUser);

    $this->testUser->givePermissionTo('edit-articles');

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-news'))->toEqual(403);
});

it('a client cannot access a route protected by the permission middleware if have a different permission', function () {
    if ($this->getLaravelVersion() < 9) {
        $this->markTestSkipped('requires laravel >= 9');
    }

    Passport::actingAsClient($this->testClient, ['*']);

    $this->testClient->givePermissionTo('edit-posts');

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-news', null, true))->toEqual(403);
});

it('a user cannot access a route protected by permission middleware if have not permissions', function () {
    Auth::login($this->testUser);

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-articles|edit-news'))->toEqual(403);
});

it('a client cannot access a route protected by permission middleware if have not permissions', function () {
    if ($this->getLaravelVersion() < 9) {
        $this->markTestSkipped('requires laravel >= 9');
    }

    Passport::actingAsClient($this->testClient, ['*']);

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-articles|edit-posts', null, true))->toEqual(403);
});

it('a user can access a route protected by permission middleware if has permission via role', function () {
    Auth::login($this->testUser);

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-articles'))->toEqual(403);

    $this->testUserRole->givePermissionTo('edit-articles');
    $this->testUser->assignRole('testRole');

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-articles'))->toEqual(200);
});

it('a client can access a route protected by permission middleware if has permission via role', function () {
    if ($this->getLaravelVersion() < 9) {
        $this->markTestSkipped('requires laravel >= 9');
    }

    Passport::actingAsClient($this->testClient, ['*']);

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-articles', null, true))->toEqual(403);

    $this->testClientRole->givePermissionTo('edit-posts');
    $this->testClient->assignRole('clientRole');

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-posts', null, true))->toEqual(200);
});

it('the required permissions can be fetched from the exception', function () {
    Auth::login($this->testUser);

    $message = null;
    $requiredPermissions = [];

    try {
        $this->permissionMiddleware->handle(new Request, function () {
            return (new Response)->setContent('<html></html>');
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
        $this->permissionMiddleware->handle(new Request, function () {
            return (new Response)->setContent('<html></html>');
        }, 'some-permission');
    } catch (UnauthorizedException $e) {
        $message = $e->getMessage();
    }

    expect($message)->toEndWith('Necessary permissions are some-permission');
});

it('use not existing custom guard in permission', function () {
    $class = null;

    try {
        $this->permissionMiddleware->handle(new Request, function () {
            return (new Response)->setContent('<html></html>');
        }, 'edit-articles', 'xxx');
    } catch (InvalidArgumentException $e) {
        $class = get_class($e);
    }

    expect($class)->toEqual(InvalidArgumentException::class);
});

it('user can not access permission with guard admin while login using default guard', function () {
    Auth::login($this->testUser);

    $this->testUser->givePermissionTo('edit-articles');

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-articles', 'admin'))->toEqual(403);
});

it('client can not access permission with guard admin while login using default guard', function () {
    if ($this->getLaravelVersion() < 9) {
        $this->markTestSkipped('requires laravel >= 9');
    }

    Passport::actingAsClient($this->testClient, ['*']);

    $this->testClient->givePermissionTo('edit-posts');

    expect($this->runMiddleware($this->permissionMiddleware, 'edit-posts', 'admin', true))->toEqual(403);
});

it('user can access permission with guard admin while login using admin guard', function () {
    Auth::guard('admin')->login($this->testAdmin);

    $this->testAdmin->givePermissionTo('admin-permission');

    expect($this->runMiddleware($this->permissionMiddleware, 'admin-permission', 'admin'))->toEqual(200);
});

it('the middleware can be created with static using method', function () {
    expect(PermissionMiddleware::using('edit-articles'))
        ->toBe('Spatie\Permission\Middleware\PermissionMiddleware:edit-articles');

    expect(PermissionMiddleware::using('edit-articles', 'my-guard'))
        ->toEqual('Spatie\Permission\Middleware\PermissionMiddleware:edit-articles,my-guard');

    expect(PermissionMiddleware::using(['edit-articles', 'edit-news']))
        ->toEqual('Spatie\Permission\Middleware\PermissionMiddleware:edit-articles|edit-news');
});

it('the middleware can handle enum based permissions with static using method', function () {
    expect(PermissionMiddleware::using(TestModels\TestRolePermissionsEnum::VIEWARTICLES))
        ->toBe('Spatie\Permission\Middleware\PermissionMiddleware:view articles');

    expect(PermissionMiddleware::using(TestModels\TestRolePermissionsEnum::VIEWARTICLES, 'my-guard'))
        ->toEqual('Spatie\Permission\Middleware\PermissionMiddleware:view articles,my-guard');

    expect(PermissionMiddleware::using([TestModels\TestRolePermissionsEnum::VIEWARTICLES, TestModels\TestRolePermissionsEnum::EDITARTICLES]))
        ->toEqual('Spatie\Permission\Middleware\PermissionMiddleware:view articles|edit articles');
})->skip(PHP_VERSION_ID < 80100, 'Requires PHP >= 8.1');

it('the middleware can handle enum based permissions with handle method', function () {
    app(Permission::class)->create(['name' => TestModels\TestRolePermissionsEnum::VIEWARTICLES->value]);
    app(Permission::class)->create(['name' => TestModels\TestRolePermissionsEnum::EDITARTICLES->value]);

    Auth::login($this->testUser);
    $this->testUser->givePermissionTo(TestModels\TestRolePermissionsEnum::VIEWARTICLES);

    expect($this->runMiddleware($this->permissionMiddleware, TestModels\TestRolePermissionsEnum::VIEWARTICLES))
        ->toEqual(200);

    $this->testUser->givePermissionTo(TestModels\TestRolePermissionsEnum::EDITARTICLES);

    expect($this->runMiddleware($this->permissionMiddleware, [TestModels\TestRolePermissionsEnum::VIEWARTICLES, TestModels\TestRolePermissionsEnum::EDITARTICLES]))
        ->toEqual(200);
})->skip(PHP_VERSION_ID < 80100, 'Requires PHP >= 8.1');
