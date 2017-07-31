<?php

namespace Spatie\Permission\Test;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class MiddlewareTest extends TestCase
{
    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function it_can_not_access_with_middleware_role_if_is_guest()
    {
        $request = new Request();
        $middleware = new RoleMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, 'testRole');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function it_can_not_access_with_middleware_permission_if_is_guest()
    {
        $request = new Request();
        $middleware = new PermissionMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, $this->testUserPermission->name);
    }

    /** @test */
    public function it_can_access_if_the_user_has_role()
    {
        Auth::login($this->testUser);
        $this->testUser->assignRole('testRole');

        $request = new Request();
        $middleware = new RoleMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, 'testRole');

        $this->assertEquals($result->status(), 200);
    }

    /** @test */
    public function it_can_access_if_the_user_has_one_of_two_roles()
    {
        Auth::login($this->testUser);
        $this->testUser->assignRole('testRole');

        $request = new Request();
        $middleware = new RoleMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, 'testRole|testRole2');

        $this->assertEquals($result->status(), 200);
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function it_can_not_access_if_the_user_has_not_role()
    {
        Auth::login($this->testUser);
        $this->testUser->assignRole(['testRole']);

        $request = new Request();
        $middleware = new RoleMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, 'testRole2');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function it_can_not_access_if_the_user_has_any_of_two_roles()
    {
        Auth::login($this->testUser);
        $request = new Request();
        $middleware = new RoleMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, 'testRole|testrole2');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function it_can_not_access_if_middleware_does_not_has_role()
    {
        Auth::login($this->testUser);
        $request = new Request();
        $middleware = new RoleMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, '');
    }

    /** @test */
    public function it_can_access_if_the_user_has_permission()
    {
        Auth::login($this->testUser);
        $this->testUser->givePermissionTo('edit-articles');

        $request = new Request();
        $middleware = new PermissionMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, 'edit-articles');

        $this->assertEquals($result->status(), 200);
    }

    /** @test */
    public function it_can_access_if_the_user_has_one_of_two_permission()
    {
        Auth::login($this->testUser);
        $this->testUser->givePermissionTo('edit-articles');

        $request = new Request();
        $middleware = new PermissionMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, 'edit-articles|edit-news');

        $this->assertEquals($result->status(), 200);
    }


    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function it_can_not_access_if_the_user_has_not_permisson()
    {
        Auth::login($this->testUser);
        $request = new Request();
        $middleware = new PermissionMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, 'edit-articles');
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function it_can_not_access_if_the_user_has_any_of_two_permissions()
    {
        Auth::login($this->testUser);
        $request = new Request();
        $middleware = new PermissionMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, 'edit-articles|edit-news');
    }

    /**
     * @test
     * @expectedException Spatie\Permission\Exceptions\PermissionDoesNotExist
     */
    public function it_can_not_access_if_middleware_does_not_has_permission()
    {
        Auth::login($this->testUser);
        $request = new Request();
        $middleware = new PermissionMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, '');
    }
}
