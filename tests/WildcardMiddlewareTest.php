<?php

namespace Spatie\Permission\Tests;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Spatie\Permission\Middlewares\RoleOrPermissionMiddleware;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->roleMiddleware = new RoleMiddleware();

    $this->permissionMiddleware = new PermissionMiddleware();

    $this->roleOrPermissionMiddleware = new RoleOrPermissionMiddleware();

    app('config')->set('permission.enable_wildcard_permission', true);
});

it('a guest cannot access a route protected by the permission middleware', function () {
    expect(runMiddleware($this->permissionMiddleware, 'articles.edit'))->toEqual(403);
});

it('a user can access a route protected by permission middleware if have this permission', function () {
    Auth::login($this->testUser);

    Permission::create(['name' => 'articles']);

    $this->testUser->givePermissionTo('articles');

    expect(runMiddleware($this->permissionMiddleware, 'articles.edit'))->toEqual(200);
});

it('a user can access a route protected by this permission middleware if have one of the permissions', function () {
    Auth::login($this->testUser);

    Permission::create(['name' => 'articles.*.test']);

    $this->testUser->givePermissionTo('articles.*.test');

    expect(runMiddleware($this->permissionMiddleware, 'news.edit|articles.create.test'))->toEqual(200);

    expect(runMiddleware($this->permissionMiddleware, ['news.edit', 'articles.create.test']))->toEqual(200);
});

it('a user cannot access a route protected by the permission middleware if have a different permission', function () {
    Auth::login($this->testUser);

    Permission::create(['name' => 'articles.*']);

    $this->testUser->givePermissionTo('articles.*');

    expect(runMiddleware($this->permissionMiddleware, 'news.edit'))->toEqual(403);
});

it('a user cannot access a route protected by permission middleware if have not permissions', function () {
    Auth::login($this->testUser);

    expect(runMiddleware($this->permissionMiddleware, 'articles.edit|news.edit'))->toEqual(403);
});

it('a user can access a route protected by permission or role middleware if has this permission or role', function () {
    Auth::login($this->testUser);

    Permission::create(['name' => 'articles.*']);

    $this->testUser->assignRole('testRole');
    $this->testUser->givePermissionTo('articles.*');

    expect(runMiddleware($this->roleOrPermissionMiddleware, 'testRole|news.edit|articles.create'))->toEqual(200);

    $this->testUser->removeRole('testRole');

    expect(runMiddleware($this->roleOrPermissionMiddleware, 'testRole|articles.edit'))->toEqual(200);

    $this->testUser->revokePermissionTo('articles.*');
    $this->testUser->assignRole('testRole');

    expect(runMiddleware($this->roleOrPermissionMiddleware, 'testRole|articles.edit'))->toEqual(200);

    expect(runMiddleware($this->roleOrPermissionMiddleware, ['testRole', 'articles.edit']))->toEqual(200);
});

it('the required permissions can be fetched from the exception', function () {
    Auth::login($this->testUser);

    $requiredPermissions = [];

    try {
        $this->permissionMiddleware->handle(new Request(), function () {
        return (new Response())->setContent('<html></html>');
        }, 'permission.some');
    } catch (UnauthorizedException $e) {
        $requiredPermissions = $e->getRequiredPermissions();
    }

    expect($requiredPermissions)->toEqual(['permission.some']);
});
