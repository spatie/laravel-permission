<?php

use Spatie\Permission\Tests\TestSupport\TestModels\TestRolePermissionsEnum;

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
