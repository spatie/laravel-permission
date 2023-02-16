<?php

namespace Spatie\Permission\Tests;

use Illuminate\Http\Response;

it('test role function', function () {
    $router = getRouter();

    $router->get('role-test', getRouteResponse())
        ->name('role.test')
        ->role('superadmin');

    expect(getLastRouteMiddlewareFromRouter($router))->toEqual(['role:superadmin']);
});

it('test permission function', function () {
    $router = getRouter();

    $router->get('permission-test', getRouteResponse())
        ->name('permission.test')
        ->permission(['edit articles', 'save articles']);

    expect(getLastRouteMiddlewareFromRouter($router))->toEqual(['permission:edit articles|save articles']);
});

it('test role and permission function together', function () {
    $router = getRouter();

    $router->get('role-permission-test', getRouteResponse())
        ->name('role-permission.test')
        ->role('superadmin|admin')
        ->permission('create user|edit user');

    expect(getLastRouteMiddlewareFromRouter($router))->toEqual(
        [
            'role:superadmin|admin',
            'permission:create user|edit user',
        ],
    );
});
