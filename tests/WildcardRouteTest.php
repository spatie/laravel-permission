<?php

use Spatie\Permission\Tests\TestCase;

uses(TestCase::class);

it('test permission function', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $router = $this->getRouter();

    $router->get('permission-test', $this->getRouteResponse())
        ->name('permission.test')
        ->permission(['articles.edit', 'articles.save']);

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual(['permission:articles.edit|articles.save']);
});

it('test role and permission function together', function () {
    app('config')->set('permission.enable_wildcard_permission', true);

    $router = $this->getRouter();

    $router->get('role-permission-test', $this->getRouteResponse())
        ->name('role-permission.test')
        ->role('superadmin|admin')
        ->permission('user.create|user.edit');

    expect($this->getLastRouteMiddlewareFromRouter($router))->toEqual([
        'role:superadmin|admin',
        'permission:user.create|user.edit',
    ]);
});
