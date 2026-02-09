<?php

use Spatie\Permission\Tests\TestCase;
use Spatie\Permission\Tests\TestModels\TestRolePermissionsEnum;

uses(TestCase::class);

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
        ->role(TestRolePermissionsEnum::USERMANAGER);

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(['role:'.TestRolePermissionsEnum::USERMANAGER->value]);
})->skip(PHP_VERSION_ID < 80100, 'Requires PHP >= 8.1');

it('test permission function with backed enum', function () {
    $router = $this->getRouter();

    $router->get('permission-test.enum', $this->getRouteResponse())
        ->name('permission.test.enum')
        ->permission(TestRolePermissionsEnum::WRITER);

    $expected = ['permission:'.TestRolePermissionsEnum::WRITER->value];
    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual($expected);
})->skip(PHP_VERSION_ID < 80100, 'Requires PHP >= 8.1');

it('test role and permission function together with backed enum', function () {
    $router = $this->getRouter();

    $router->get('roles-permissions-test.enum', $this->getRouteResponse())
        ->name('roles-permissions.test.enum')
        ->role([TestRolePermissionsEnum::USERMANAGER, TestRolePermissionsEnum::ADMIN])
        ->permission([TestRolePermissionsEnum::WRITER, TestRolePermissionsEnum::EDITOR]);

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual([
        'role:'.TestRolePermissionsEnum::USERMANAGER->value.'|'.TestRolePermissionsEnum::ADMIN->value,
        'permission:'.TestRolePermissionsEnum::WRITER->value.'|'.TestRolePermissionsEnum::EDITOR->value,
    ]);
})->skip(PHP_VERSION_ID < 80100, 'Requires PHP >= 8.1');

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
        ->roleOrPermission(TestRolePermissionsEnum::USERMANAGER);

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(['role_or_permission:'.TestRolePermissionsEnum::USERMANAGER->value]);
})->skip(PHP_VERSION_ID < 80100, 'Requires PHP >= 8.1');

it('test role or permission function with backed enum array', function () {
    $router = $this->getRouter();

    $router->get('role-or-permission-array-test.enum', $this->getRouteResponse())
        ->name('role-or-permission-array.test.enum')
        ->roleOrPermission([TestRolePermissionsEnum::USERMANAGER, TestRolePermissionsEnum::EDITARTICLES]); // @phpstan-ignore-line

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(
        ['role_or_permission:'.TestRolePermissionsEnum::USERMANAGER->value.'|'.TestRolePermissionsEnum::EDITARTICLES->value] // @phpstan-ignore-line
    );
})->skip(PHP_VERSION_ID < 80100, 'Requires PHP >= 8.1');
