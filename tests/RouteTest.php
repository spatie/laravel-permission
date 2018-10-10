<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Response;

class RouteTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        if (! $this->isVersionAvailable()) {
            $this->markTestSkipped(
                'This feature available for Laravel 5.5 and higher'
            );
        }
    }

    /** @test */
    public function test_role_function()
    {
        $router = $this->getRouter();

        $router->get('role-test', $this->getRouteResponse())
                ->name('role.test')
                ->role('superadmin');

        $this->assertEquals(['role:superadmin'], $this->getLastRouteMiddlewareFromRouter($router));
    }

    /** @test */
    public function test_permission_function()
    {
        $router = $this->getRouter();

        $router->get('permission-test', $this->getRouteResponse())
                ->name('permission.test')
                ->permission(['edit articles', 'save articles']);

        $this->assertEquals(['permission:edit articles|save articles'], $this->getLastRouteMiddlewareFromRouter($router));
    }

    /** @test */
    public function test_role_and_permission_function_together()
    {
        $router = $this->getRouter();

        $router->get('role-permission-test', $this->getRouteResponse())
                ->name('role-permission.test')
                ->role('superadmin|admin')
                ->permission('create user|edit user');

        $this->assertEquals(
            [
                'role:superadmin|admin',
                'permission:create user|edit user',
            ],
            $this->getLastRouteMiddlewareFromRouter($router)
        );
    }

    protected function isVersionAvailable()
    {
        return app()->version() >= '5.5';
    }

    protected function getLastRouteMiddlewareFromRouter($router)
    {
        return last($router->getRoutes()->get())->middleware();
    }

    protected function getRouter()
    {
        return app('router');
    }

    protected function getRouteResponse()
    {
        return function () {
            return (new Response())->setContent('<html></html>');
        };
    }
}
