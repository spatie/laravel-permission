<?php

namespace Spatie\Permission\Test;

use Auth;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class MiddlewareTest extends TestCase
{
    protected $RoleMiddleware;
    protected $PermissionMiddleware;

    public function setUp()
    {
        parent::setUp();

        $this->RoleMiddleware = new RoleMiddleware($this->app);
        $this->PermissionMiddleware = new PermissionMiddleware($this->app);
    }

    /**
     * @test
     */
    public function a_guest_cannot_access_a_route_protected_by_the_role_middleware()
    {
        $this->assertEquals(
            (new TestHelper)->testMiddleware(
                $this->RoleMiddleware, 'testRole'
            ), 403);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_role_middleware_if_have_this_role()
    {
        Auth::login($this->testUser);
        $this->testUser->assignRole('testRole');

        $this->assertEquals(
            (new TestHelper)->testMiddleware(
                $this->RoleMiddleware, 'testRole'
            ), 200);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_this_role_middleware_if_have_one_of_the_roles()
    {
        Auth::login($this->testUser);
        $this->testUser->assignRole('testRole');

        $this->assertEquals(
            (new TestHelper)->testMiddleware(
                $this->RoleMiddleware, 'testRole|testRole2'
            ), 200);
    }

    /**
     * @test
     */
    public function a_user_cannot_access_a_route_protected_by_the_role_middleware_if_have_a_different_role()
    {
        Auth::login($this->testUser);
        $this->testUser->assignRole(['testRole']);

        $this->assertEquals(
            (new TestHelper)->testMiddleware(
                $this->RoleMiddleware, 'testRole2'
            ), 403);
    }

    /**
     * @test
     */
    public function a_user_cannot_access_a_route_protected_by_role_middleware_if_have_not_roles()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            (new TestHelper)->testMiddleware(
                $this->RoleMiddleware, 'testRole|testRole2'
            ), 403);
    }

    /**
     * @test
     */
    public function a_user_cannot_access_a_route_protected_by_role_middleware_if_role_is_undefined()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            (new TestHelper)->testMiddleware(
                $this->RoleMiddleware, ''
            ), 403);
    }

    /**
     * @test
     */
    public function a_guest_cannot_access_a_route_protected_by_the_permission_middleware()
    {
        $this->assertEquals(
            (new TestHelper)->testMiddleware(
                $this->PermissionMiddleware, 'edit-articles'
            ), 403);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_permission_middleware_if_have_this_permission()
    {
        Auth::login($this->testUser);
        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            (new TestHelper)->testMiddleware(
                $this->PermissionMiddleware, 'edit-articles'
            ), 200);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_this_permission_middleware_if_have_one_of_the_permissions()
    {
        Auth::login($this->testUser);
        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            (new TestHelper)->testMiddleware(
                $this->PermissionMiddleware, 'edit-articles|edit-news'
            ), 200);
    }

    /**
     * @test
     */
    public function a_user_cannot_access_a_route_protected_by_the_permission_middleware_if_have_a_different_permission()
    {
        Auth::login($this->testUser);
        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            (new TestHelper)->testMiddleware(
                $this->PermissionMiddleware, 'edit-news'
            ), 403);
    }

    /**
     * @test
     */
    public function a_user_cannot_access_a_route_protected_by_permission_middleware_if_have_not_permissions()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            (new TestHelper)->testMiddleware(
                $this->PermissionMiddleware, 'edit-articles|edit-news'
            ), 403);
    }
}
