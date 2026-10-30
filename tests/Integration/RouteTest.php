<?php

use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum;

beforeEach(function () {
    $router = $this->getRouter();
    $router->aliasMiddleware('role', RoleMiddleware::class);
    $router->aliasMiddleware('permission', PermissionMiddleware::class);
    $router->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
});

it('test role function', function () {
    $router = $this->getRouter();

    $router->get('role-test', $this->getRouteResponse())
        ->name('role.test')
        ->role('superadmin');

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(['role:superadmin']);
});

it('test permission function', function () {
    $router = $this->getRouter();

    $router->get('permission-test', $this->getRouteResponse())
        ->name('permission.test')
        ->permission(['edit articles', 'save articles']);

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(['permission:edit articles|save articles']);
});

it('test role and permission function together', function () {
    $router = $this->getRouter();

    $router->get('role-permission-test', $this->getRouteResponse())
        ->name('role-permission.test')
        ->role('superadmin|admin')
        ->permission('create user|edit user');

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual([
        'role:superadmin|admin',
        'permission:create user|edit user',
    ]);
});

it('test role function with backed enum', function () {
    $router = $this->getRouter();

    $router->get('role-test.enum', $this->getRouteResponse())
        ->name('role.test.enum')
        ->role(TestRolePermissionsEnum::UserManager);

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(['role:'.TestRolePermissionsEnum::UserManager->value]);
});

it('test permission function with backed enum', function () {
    $router = $this->getRouter();

    $router->get('permission-test.enum', $this->getRouteResponse())
        ->name('permission.test.enum')
        ->permission(TestRolePermissionsEnum::Writer);

    $expected = ['permission:'.TestRolePermissionsEnum::Writer->value];
    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual($expected);
});

it('test role and permission function together with backed enum', function () {
    $router = $this->getRouter();

    $router->get('roles-permissions-test.enum', $this->getRouteResponse())
        ->name('roles-permissions.test.enum')
        ->role([TestRolePermissionsEnum::UserManager, TestRolePermissionsEnum::Admin])
        ->permission([TestRolePermissionsEnum::Writer, TestRolePermissionsEnum::Editor]);

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual([
        'role:'.TestRolePermissionsEnum::UserManager->value.'|'.TestRolePermissionsEnum::Admin->value,
        'permission:'.TestRolePermissionsEnum::Writer->value.'|'.TestRolePermissionsEnum::Editor->value,
    ]);
});

it('test role or permission function', function () {
    $router = $this->getRouter();

    $router->get('role-or-permission-test', $this->getRouteResponse())
        ->name('role-or-permission.test')
        ->roleOrPermission('admin|edit articles');

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(['role_or_permission:admin|edit articles']);
});

it('test role or permission function with array', function () {
    $router = $this->getRouter();

    $router->get('role-or-permission-array-test', $this->getRouteResponse())
        ->name('role-or-permission-array.test')
        ->roleOrPermission(['admin', 'edit articles']);

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(['role_or_permission:admin|edit articles']);
});

it('test role or permission function with backed enum', function () {
    $router = $this->getRouter();

    $router->get('role-or-permission-test.enum', $this->getRouteResponse())
        ->name('role-or-permission.test.enum')
        ->roleOrPermission(TestRolePermissionsEnum::UserManager);

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(['role_or_permission:'.TestRolePermissionsEnum::UserManager->value]);
});

it('test role or permission function with backed enum array', function () {
    $router = $this->getRouter();

    $router->get('role-or-permission-array-test.enum', $this->getRouteResponse())
        ->name('role-or-permission-array.test.enum')
        ->roleOrPermission([TestRolePermissionsEnum::UserManager, TestRolePermissionsEnum::EditArticles]); // @phpstan-ignore-line

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(
        ['role_or_permission:'.TestRolePermissionsEnum::UserManager->value.'|'.TestRolePermissionsEnum::EditArticles->value] // @phpstan-ignore-line
    );
});

it('test role function on route group', function () {
    $router = $this->getRouter();

    $router->prefix('admin')->role('superadmin')->group(function () use ($router) {
        $router->get('role-group-test', $this->getRouteResponse())->name('role-group.test');
    });

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(['role:superadmin']);
});

it('test permission function on route group', function () {
    $router = $this->getRouter();

    $router->prefix('articles')->permission(['edit articles', 'save articles'])->group(function () use ($router) {
        $router->get('permission-group-test', $this->getRouteResponse())->name('permission-group.test');
    });

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(['permission:edit articles|save articles']);
});

it('test role or permission function on route group', function () {
    $router = $this->getRouter();

    $router->prefix('articles')->roleOrPermission(['admin', 'edit articles'])->group(function () use ($router) {
        $router->get('role-or-permission-group-test', $this->getRouteResponse())->name('role-or-permission-group.test');
    });

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(['role_or_permission:admin|edit articles']);
});

it('test permission function on route group with backed enum', function () {
    $router = $this->getRouter();

    $router->prefix('articles')->permission([TestRolePermissionsEnum::Writer, TestRolePermissionsEnum::Editor])->group(function () use ($router) {
        $router->get('permission-group-test.enum', $this->getRouteResponse())->name('permission-group.test.enum');
    });

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual([
        'permission:'.TestRolePermissionsEnum::Writer->value.'|'.TestRolePermissionsEnum::Editor->value,
    ]);
});

it('test role and permission functions on router', function () {
    $router = $this->getRouter();

    $router->role('superadmin')->permission('edit articles')->group(function () use ($router) {
        $router->get('router-group-test', $this->getRouteResponse())->name('router-group.test');
    });

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual([
        'role:superadmin',
        'permission:edit articles',
    ]);
});

it('test role and permission functions on route group keep existing middleware', function () {
    $router = $this->getRouter();

    $router->middleware('auth')->role('superadmin')->permission('edit articles')->group(function () use ($router) {
        $router->get('route-group-middleware-test', $this->getRouteResponse())->name('route-group-middleware.test');
    });

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual([
        'auth',
        'role:superadmin',
        'permission:edit articles',
    ]);
});

it('enforces group macros after auth and retains them in nested middleware groups', function (string $macro, string $requirement, string $grantMethod, string $grant) {
    $router = $this->getRouter();

    $router->middleware('auth')->{$macro}($requirement)->group(function () use ($router) {
        $router->get('direct', fn () => 'allowed');

        $router->middleware('permission:edit-news')->group(function () use ($router) {
            $router->get('nested', fn () => 'allowed');
        });
    });
    $router->get('outside', fn () => 'public');

    $this->get('/outside')->assertOk();
    $this->getJson('/direct')->assertUnauthorized();
    $this->getJson('/nested')->assertUnauthorized();

    $this->actingAs($this->testUser);
    $this->get('/direct')->assertForbidden();
    $this->get('/nested')->assertForbidden();

    $this->testUser->{$grantMethod}($grant);
    $this->get('/direct')->assertOk()->assertSee('allowed');
    $this->get('/nested')->assertForbidden();

    $this->testUser->givePermissionTo('edit-news');
    $this->get('/nested')->assertOk()->assertSee('allowed');

    $this->testUser->{$grantMethod === 'assignRole' ? 'removeRole' : 'revokePermissionTo'}($grant);
    $this->get('/nested')->assertForbidden();
})->with([
    ['role', 'testRole|testRole2', 'assignRole', 'testRole2'],
    ['permission', 'edit-blog|edit-articles', 'givePermissionTo', 'edit-articles'],
    ['roleOrPermission', 'testRole|edit-articles', 'givePermissionTo', 'edit-articles'],
]);

it('uses Laravel middleware replacement on registrars but appends on individual routes', function (string $macro) {
    $router = $this->getRouter();

    $router->{$macro}('unassigned')->middleware('auth')->group(function () use ($router) {
        $router->get('group', fn () => 'allowed');
    });
    $router->get('individual', fn () => 'allowed')->{$macro}('unassigned')->middleware('auth');

    $this->getJson('/group')->assertUnauthorized();

    $this->actingAs($this->testUser);
    $this->get('/group')->assertOk()->assertSee('allowed');
    $this->get('/individual')->assertForbidden();
})->with(['role', 'permission', 'roleOrPermission', 'canAny', 'canAll']);

it('registers canAny and canAll as permission middleware on routes, registrars and the router', function (string $macro, array $expected) {
    $router = $this->getRouter();

    $router->get('route', fn () => 'allowed')->{$macro}(['edit articles', 'save articles']);
    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual($expected);

    $router->middleware('auth')->{$macro}(['edit articles', 'save articles'])->group(function () use ($router) {
        $router->get('registrar', fn () => 'allowed');
    });
    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(['auth', ...$expected]);

    $router->{$macro}(['edit articles', 'save articles'])->group(function () use ($router) {
        $router->get('router', fn () => 'allowed');
    });
    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual($expected);
})->with([
    ['canAny', ['permission:edit articles|save articles']],
    ['canAll', ['permission:edit articles', 'permission:save articles']],
]);

it('accepts backed enums in canAny and canAll', function (string $macro, array $expected) {
    $router = $this->getRouter();

    $router->get('route', fn () => 'allowed')->{$macro}([TestRolePermissionsEnum::Writer, TestRolePermissionsEnum::Editor]);

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual($expected);
})->with([
    ['canAny', ['permission:'.TestRolePermissionsEnum::Writer->value.'|'.TestRolePermissionsEnum::Editor->value]],
    ['canAll', ['permission:'.TestRolePermissionsEnum::Writer->value, 'permission:'.TestRolePermissionsEnum::Editor->value]],
]);

it('requires any listed permission for canAny and every listed permission for canAll', function (string $macro, bool $allowedWithOnePermission) {
    $router = $this->getRouter();

    $router->middleware('auth')->{$macro}(['edit-articles', 'edit-news'])->group(function () use ($router) {
        $router->get('group', fn () => 'allowed');
    });
    $router->get('individual', fn () => 'allowed')->middleware('auth')->{$macro}(['edit-articles', 'edit-news']);

    $this->getJson('/group')->assertUnauthorized();
    $this->getJson('/individual')->assertUnauthorized();

    $this->actingAs($this->testUser);
    $this->get('/group')->assertForbidden();
    $this->get('/individual')->assertForbidden();

    $this->testUser->givePermissionTo('edit-articles');
    $this->get('/group')->assertStatus($allowedWithOnePermission ? 200 : 403);
    $this->get('/individual')->assertStatus($allowedWithOnePermission ? 200 : 403);

    $this->testUser->givePermissionTo('edit-news');
    $this->get('/group')->assertOk()->assertSee('allowed');
    $this->get('/individual')->assertOk()->assertSee('allowed');
})->with([
    ['canAny', true],
    ['canAll', false],
]);
