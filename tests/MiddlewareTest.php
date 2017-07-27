<?php

namespace Spatie\Permission\Test;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class MiddelwareTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Auth::login($this->testUser);
    }

    /** @test */
    public function it_can_access_if_user_has_role()
    {
        $this->testUser->assignRole('testRole');

        $request = new Request();
        $middleware = new RoleMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, 'testRole');

        $this->assertEquals($result->status(), 200);
    }

    /** @test */
    public function it_can_access_if_user_has_one_of_two_roles()
    {
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
    public function it_can_not_access_if_user_has_not_role()
    {
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
    public function it_can_not_access_if_user_has_any_of_two_roles()
    {
        $request = new Request();
        $middleware = new RoleMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, 'testRole|testrole2');
    }

    /** @test */
    public function it_can_access_if_user_has_permiss()
    {
        $this->testUser->givePermissionTo($this->testUserPermission);

        $request = new Request();
        $middleware = new PermissionMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, $this->testUserPermission->name);

        $this->assertEquals($result->status(), 200);
    }

    /**
     * @test
     * @expectedException Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function it_can_not_access_if_user_has_not_permiss()
    {
        $request = new Request();
        $middleware = new PermissionMiddleware($this->app);

        $result = $middleware->handle($request, function ($request) {
            return (new Response())->setContent('<html></html>');
        }, $this->testUserPermission->name);

        $this->assertEquals($result->status(), 200);
    }
}
