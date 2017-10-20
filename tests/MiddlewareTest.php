<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MiddlewareTest extends TestCase
{
    protected $roleMiddleware;
    protected $permissionMiddleware;

    public function setUp()
    {
        parent::setUp();

        $this->roleMiddleware = new RoleMiddleware($this->app);

        $this->permissionMiddleware = new PermissionMiddleware($this->app);
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_the_role_middleware()
    {
        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testRole'
            ), 403);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_role_middleware_if_have_this_role()
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole('testRole');

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testRole'
            ), 200);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_this_role_middleware_if_have_one_of_the_roles()
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole('testRole');

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testRole|testRole2'
            ), 200);

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, ['testRole2', 'testRole']
            ), 200);
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_the_role_middleware_if_have_a_different_role()
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole(['testRole']);

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testRole2'
            ), 403);
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_role_middleware_if_have_not_roles()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testRole|testRole2'
            ), 403);
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_role_middleware_if_role_is_undefined()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, ''
            ), 403);
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_the_permission_middleware()
    {
        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-articles'
            ), 403);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_permission_middleware_if_have_this_permission()
    {
        Auth::login($this->testUser);

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-articles'
            ), 200);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_this_permission_middleware_if_have_one_of_the_permissions()
    {
        Auth::login($this->testUser);

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-news|edit-articles'
            ), 200);

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, ['edit-news', 'edit-articles']
            ), 200);
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_the_permission_middleware_if_have_a_different_permission()
    {
        Auth::login($this->testUser);

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-news'
            ), 403);
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_permission_middleware_if_have_not_permissions()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'edit-articles|edit-news'
            ), 403);
    }

    protected function runMiddleware($middleware, $parameter)
    {
        try {
            return $middleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, $parameter)->status();
        } catch (UnauthorizedException $e) {
            return $e->getStatusCode();
        }
    }
}
