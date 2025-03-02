<?php

namespace Spatie\Permission\Tests;

use PHPUnit\Framework\Attributes\Test;

class RouteTest extends TestCase
{
    /** @test */
    #[Test]
    public function test_role_function()
    {
        $router = $this->getRouter();

        $router->get('role-test', $this->getRouteResponse())
            ->name('role.test')
            ->role('superadmin');

        $this->assertEquals(['role:superadmin'], $this->getLastRouteMiddlewareFromRouter($router));
    }

    /** @test */
    #[Test]
    public function test_permission_function()
    {
        $router = $this->getRouter();

        $router->get('permission-test', $this->getRouteResponse())
            ->name('permission.test')
            ->permission(['edit articles', 'save articles']);

        $this->assertEquals(['permission:edit articles|save articles'], $this->getLastRouteMiddlewareFromRouter($router));
    }

    /** @test */
    #[Test]
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
}
