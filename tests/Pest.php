<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(TestCase::class)->in('.');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function runMiddleware($middleware, $permission, $guard = null)
{
    try {
        return $middleware->handle(new Request(), function () {
            return (new Response())->setContent('<html></html>');
        }, $permission, $guard)->status();
    } catch (UnauthorizedException $e) {
        return $e->getStatusCode();
    }
}

function getLastRouteMiddlewareFromRouter($router)
{
    return last($router->getRoutes()->get())->middleware();
}

function getRouter()
{
    return app('router');
}

function getRouteResponse(): \Closure
{
    return function () {
        return (new Response())->setContent('<html></html>');
    };
}
