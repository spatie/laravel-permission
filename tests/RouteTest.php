<?php

namespace Spatie\Permission\Tests;

use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Tests\TestModels\TestRolePermissionsEnum;

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

    /**
     * @test
     *
     * @requires PHP 8.1.0
     */
    #[RequiresPhp('>= 8.1.0')]
    #[Test]
    public function test_role_function_with_backed_enum()
    {
        $router = $this->getRouter();

        $router->get('role-test.enum', $this->getRouteResponse())
            ->name('role.test.enum')
            ->role(TestRolePermissionsEnum::USERMANAGER);

        $this->assertEquals(['role:'.TestRolePermissionsEnum::USERMANAGER->value], $this->getLastRouteMiddlewareFromRouter($router));
    }

    /**
     * @test
     *
     * @requires PHP 8.1.0
     */
    #[RequiresPhp('>= 8.1.0')]
    #[Test]
    public function test_permission_function_with_backed_enum()
    {
        $router = $this->getRouter();

        $router->get('permission-test.enum', $this->getRouteResponse())
            ->name('permission.test.enum')
            ->permission(TestRolePermissionsEnum::WRITER);

        $expected = ['permission:'.TestRolePermissionsEnum::WRITER->value];
        $this->assertEquals($expected, $this->getLastRouteMiddlewareFromRouter($router));
    }

    /**
     * @test
     *
     * @requires PHP 8.1.0
     */
    #[RequiresPhp('>= 8.1.0')]
    #[Test]
    public function test_role_and_permission_function_together_with_backed_enum()
    {
        $router = $this->getRouter();

        $router->get('roles-permissions-test.enum', $this->getRouteResponse())
            ->name('roles-permissions.test.enum')
            ->role([TestRolePermissionsEnum::USERMANAGER, TestRolePermissionsEnum::ADMIN])
            ->permission([TestRolePermissionsEnum::WRITER, TestRolePermissionsEnum::EDITOR]);

        $this->assertEquals(
            [
                'role:'.TestRolePermissionsEnum::USERMANAGER->value.'|'.TestRolePermissionsEnum::ADMIN->value,
                'permission:'.TestRolePermissionsEnum::WRITER->value.'|'.TestRolePermissionsEnum::EDITOR->value,
            ],
            $this->getLastRouteMiddlewareFromRouter($router)
        );
    }

    /** @test */
    #[Test]
    public function test_role_or_permission_function()
    {
        $router = $this->getRouter();

        $router->get('role-or-permission-test', $this->getRouteResponse())
            ->name('role-or-permission.test')
            ->roleOrPermission('admin|edit articles');

        $this->assertEquals(['role_or_permission:admin|edit articles'], $this->getLastRouteMiddlewareFromRouter($router));
    }

    /** @test */
    #[Test]
    public function test_role_or_permission_function_with_array()
    {
        $router = $this->getRouter();

        $router->get('role-or-permission-array-test', $this->getRouteResponse())
            ->name('role-or-permission-array.test')
            ->roleOrPermission(['admin', 'edit articles']);

        $this->assertEquals(['role_or_permission:admin|edit articles'], $this->getLastRouteMiddlewareFromRouter($router));
    }

    /**
     * @test
     *
     * @requires PHP 8.1.0
     */
    #[RequiresPhp('>= 8.1.0')]
    #[Test]
    public function test_role_or_permission_function_with_backed_enum()
    {
        $router = $this->getRouter();

        $router->get('role-or-permission-test.enum', $this->getRouteResponse())
            ->name('role-or-permission.test.enum')
            ->roleOrPermission(TestRolePermissionsEnum::USERMANAGER);

        $this->assertEquals(['role_or_permission:'.TestRolePermissionsEnum::USERMANAGER->value], $this->getLastRouteMiddlewareFromRouter($router));
    }

    /**
     * @test
     *
     * @requires PHP 8.1.0
     */
    #[RequiresPhp('>= 8.1.0')]
    #[Test]
    public function test_role_or_permission_function_with_backed_enum_array()
    {
        $router = $this->getRouter();

        $router->get('role-or-permission-array-test.enum', $this->getRouteResponse())
            ->name('role-or-permission-array.test.enum')
            ->roleOrPermission([TestRolePermissionsEnum::USERMANAGER, TestRolePermissionsEnum::EDITARTICLES]); // @phpstan-ignore-line

        $this->assertEquals(
            ['role_or_permission:'.TestRolePermissionsEnum::USERMANAGER->value.'|'.TestRolePermissionsEnum::EDITARTICLES->value], // @phpstan-ignore-line
            $this->getLastRouteMiddlewareFromRouter($router)
        );
    }
}
