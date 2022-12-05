<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\RoleMiddleware;

class RoleMiddlewareTest extends TestCase
{
    protected $roleMiddleware;

    public function setUp(): void
    {
        parent::setUp();

        $this->roleMiddleware = new RoleMiddleware();
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_rolemiddleware(): void
    {
        $this->assertEquals(
            403,
            $this->runMiddleware($this->roleMiddleware, 'testRole')
        );
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_role_middleware_of_another_guard(): void
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole('testRole');

        $this->assertEquals(
            403,
            $this->runMiddleware($this->roleMiddleware, 'testAdminRole')
        );
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_role_middleware_if_have_this_role(): void
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole('testRole');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->roleMiddleware, 'testRole')
        );
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_this_role_middleware_if_have_one_of_the_roles(): void
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole('testRole');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->roleMiddleware, 'testRole|testRole2')
        );

        $this->assertEquals(
            200,
            $this->runMiddleware($this->roleMiddleware, ['testRole2', 'testRole'])
        );
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_the_role_middleware_if_have_a_different_role(): void
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole(['testRole']);

        $this->assertEquals(
            403,
            $this->runMiddleware($this->roleMiddleware, 'testRole2')
        );
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_role_middleware_if_have_not_roles(): void
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            403,
            $this->runMiddleware($this->roleMiddleware, 'testRole|testRole2')
        );
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_role_middleware_if_role_is_undefined(): void
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            403,
            $this->runMiddleware($this->roleMiddleware, '')
        );
    }

    /** @test */
    public function the_required_roles_can_be_fetched_from_the_exception(): void
    {
        Auth::login($this->testUser);

        $message = null;
        $requiredRoles = [];

        try {
            $this->roleMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'some-role');
        } catch (UnauthorizedException $e) {
            $message = $e->getMessage();
            $requiredRoles = $e->getRequiredRoles();
        }

        $this->assertEquals('User does not have the right roles.', $message);
        $this->assertEquals(['some-role'], $requiredRoles);
    }

    /** @test */
    public function the_required_roles_can_be_displayed_in_the_exception(): void
    {
        Auth::login($this->testUser);
        Config::set(['permission.display_role_in_exception' => true]);

        $message = null;

        try {
            $this->roleMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'some-role');
        } catch (UnauthorizedException $e) {
            $message = $e->getMessage();
        }

        $this->assertStringEndsWith('Necessary roles are some-role', $message);
    }

    /** @test */
    public function use_not_existing_custom_guard_in_role(): void
    {
        $class = null;

        try {
            $this->roleMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'testRole', 'xxx');
        } catch (InvalidArgumentException $e) {
            $class = get_class($e);
        }

        $this->assertEquals(InvalidArgumentException::class, $class);
    }

    /** @test */
    public function user_can_not_access_role_with_guard_admin_while_login_using_default_guard(): void
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole('testRole');

        $this->assertEquals(
            403,
            $this->runMiddleware($this->roleMiddleware, 'testRole', 'admin')
        );
    }

    /** @test */
    public function user_can_access_role_with_guard_admin_while_login_using_admin_guard(): void
    {
        Auth::guard('admin')->login($this->testAdmin);

        $this->testAdmin->assignRole('testAdminRole');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->roleMiddleware, 'testAdminRole', 'admin')
        );
    }

    protected function runMiddleware($middleware, $roleName, $guard = null)
    {
        try {
            return $middleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, $roleName, $guard)->status();
        } catch (UnauthorizedException $e) {
            return $e->getStatusCode();
        }
    }
}
