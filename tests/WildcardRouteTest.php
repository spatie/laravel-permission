<?php

namespace Spatie\Permission\Tests;

class WildcardRouteTest extends TestCase
{
    /** @test */
    public function test_permission_function()
    {
        app('config')->set('permission.enable_wildcard_permission', true);

        $router = $this->getRouter();

        $router->get('permission-test', $this->getRouteResponse())
            ->name('permission.test')
            ->permission(['articles.edit', 'articles.save']);

        $this->assertEquals(['permission:articles.edit|articles.save'], $this->getLastRouteMiddlewareFromRouter($router));
    }

    /** @test */
    public function test_role_and_permission_function_together()
    {
        app('config')->set('permission.enable_wildcard_permission', true);

        $router = $this->getRouter();

        $router->get('role-permission-test', $this->getRouteResponse())
            ->name('role-permission.test')
            ->role('superadmin|admin')
            ->permission('user.create|user.edit');

        $this->assertEquals(
            [
                'role:superadmin|admin',
                'permission:user.create|user.edit',
            ],
            $this->getLastRouteMiddlewareFromRouter($router)
        );
    }
}
