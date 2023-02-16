<?php

namespace Spatie\Permission\Tests;

it('test permission function', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $router = getRouter();

    $router->get('permission-test', getRouteResponse())
        ->name('permission.test')
        ->permission(['articles.edit', 'articles.save']);

    expect(getLastRouteMiddlewareFromRouter($router))->toEqual(['permission:articles.edit|articles.save']);
});

it('test role and permission function together', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $router = getRouter();

    $router->get('role-permission-test', getRouteResponse())
        ->name('role-permission.test')
        ->role('superadmin|admin')
        ->permission('user.create|user.edit');

    expect(getLastRouteMiddlewareFromRouter($router))->toEqual(
        [
            'role:superadmin|admin',
            'permission:user.create|user.edit',
        ],
    );
});
