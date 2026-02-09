<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Spatie\Permission\Models\Permission;
beforeEach(function () {
    $this->roleMiddleware = new RoleMiddleware;
    $this->permissionMiddleware = new PermissionMiddleware;
    $this->roleOrPermissionMiddleware = new RoleOrPermissionMiddleware;

    app('config')->set('permission.enable_wildcard_permission', true);
});

it('does not allow a guest to access a route protected by the permission middleware', function () {
    expect($this->runMiddleware($this->permissionMiddleware, 'articles.edit'))->toBe(403);
});

it('allows a user to access a route protected by permission middleware if they have this permission', function () {
    Auth::login($this->testUser);

    Permission::create(['name' => 'articles']);

    $this->testUser->givePermissionTo('articles');

    expect($this->runMiddleware($this->permissionMiddleware, 'articles.edit'))->toBe(200);
});

it('allows a user to access a route protected by this permission middleware if they have one of the permissions', function () {
    Auth::login($this->testUser);

    Permission::create(['name' => 'articles.*.test']);

    $this->testUser->givePermissionTo('articles.*.test');

    expect($this->runMiddleware($this->permissionMiddleware, 'news.edit|articles.create.test'))->toBe(200);
    expect($this->runMiddleware($this->permissionMiddleware, ['news.edit', 'articles.create.test']))->toBe(200);
});

it('does not allow a user to access a route protected by the permission middleware if they have a different permission', function () {
    Auth::login($this->testUser);

    Permission::create(['name' => 'articles.*']);

    $this->testUser->givePermissionTo('articles.*');

    expect($this->runMiddleware($this->permissionMiddleware, 'news.edit'))->toBe(403);
});

it('does not allow a user to access a route protected by permission middleware if they have no permissions', function () {
    Auth::login($this->testUser);

    expect($this->runMiddleware($this->permissionMiddleware, 'articles.edit|news.edit'))->toBe(403);
});

it('allows a user to access a route protected by permission or role middleware if they have this permission or role', function () {
    Auth::login($this->testUser);

    Permission::create(['name' => 'articles.*']);

    $this->testUser->assignRole('testRole');
    $this->testUser->givePermissionTo('articles.*');

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|news.edit|articles.create'))->toBe(200);

    $this->testUser->removeRole('testRole');

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|articles.edit'))->toBe(200);

    $this->testUser->revokePermissionTo('articles.*');
    $this->testUser->assignRole('testRole');

    expect($this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|articles.edit'))->toBe(200);
    expect($this->runMiddleware($this->roleOrPermissionMiddleware, ['testRole', 'articles.edit']))->toBe(200);
});

it('can fetch the required permissions from the exception', function () {
    Auth::login($this->testUser);

    $requiredPermissions = [];

    try {
        $this->permissionMiddleware->handle(new Request, function () {
            return (new Response)->setContent('<html></html>');
        }, 'permission.some');
    } catch (UnauthorizedException $e) {
        $requiredPermissions = $e->getRequiredPermissions();
    }

    expect($requiredPermissions)->toBe(['permission.some']);
});
