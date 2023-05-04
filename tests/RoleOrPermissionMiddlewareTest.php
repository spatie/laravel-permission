<?php

namespace Spatie\Permission\Tests;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\RoleOrPermissionMiddleware;
use Spatie\Permission\Tests\TestModels\UserWithoutHasRoles;

class RoleOrPermissionMiddlewareTest extends TestCase
{
    protected $roleOrPermissionMiddleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleOrPermissionMiddleware = new RoleOrPermissionMiddleware();
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_the_role_or_permission_middleware()
    {
        $this->assertEquals(
            403,
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole')
        );
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_permission_or_role_middleware_if_has_this_permission_or_role()
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole('testRole');
        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-news|edit-articles')
        );

        $this->testUser->removeRole('testRole');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles')
        );

        $this->testUser->revokePermissionTo('edit-articles');
        $this->testUser->assignRole('testRole');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles')
        );

        $this->assertEquals(
            200,
            $this->runMiddleware($this->roleOrPermissionMiddleware, ['testRole', 'edit-articles'])
        );
    }

    /** @test */
    public function a_super_admin_user_can_access_a_route_protected_by_permission_or_role_middleware()
    {
        Auth::login($this->testUser);

        Gate::before(function ($user, $ability) {
            return $user->getKey() === $this->testUser->getKey() ? true : null;
        });

        $this->assertEquals(
            200,
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles')
        );
    }

    /** @test */
    public function a_user_can_not_access_a_route_protected_by_permission_or_role_middleware_if_have_not_has_roles_trait()
    {
        $userWithoutHasRoles = UserWithoutHasRoles::create(['email' => 'test_not_has_roles@user.com']);

        Auth::login($userWithoutHasRoles);

        $this->assertEquals(
            403,
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles')
        );
    }

    /** @test */
    public function a_user_can_not_access_a_route_protected_by_permission_or_role_middleware_if_have_not_this_permission_and_role()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            403,
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles')
        );

        $this->assertEquals(
            403,
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'missingRole|missingPermission')
        );
    }

    /** @test */
    public function use_not_existing_custom_guard_in_role_or_permission()
    {
        $class = null;

        try {
            $this->roleOrPermissionMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'testRole', 'xxx');
        } catch (InvalidArgumentException $e) {
            $class = get_class($e);
        }

        $this->assertEquals(InvalidArgumentException::class, $class);
    }

    /** @test */
    public function user_can_not_access_permission_or_role_with_guard_admin_while_login_using_default_guard()
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole('testRole');
        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            403,
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'edit-articles|testRole', 'admin')
        );
    }

    /** @test */
    public function user_can_access_permission_or_role_with_guard_admin_while_login_using_admin_guard()
    {
        Auth::guard('admin')->login($this->testAdmin);

        $this->testAdmin->assignRole('testAdminRole');
        $this->testAdmin->givePermissionTo('admin-permission');

        $this->assertEquals(
            200,
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'admin-permission|testAdminRole', 'admin')
        );
    }

    /** @test */
    public function the_required_permissions_or_roles_can_be_fetched_from_the_exception()
    {
        Auth::login($this->testUser);

        $message = null;
        $requiredRolesOrPermissions = [];

        try {
            $this->roleOrPermissionMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'some-permission|some-role');
        } catch (UnauthorizedException $e) {
            $message = $e->getMessage();
            $requiredRolesOrPermissions = $e->getRequiredPermissions();
        }

        $this->assertEquals('User does not have any of the necessary access rights.', $message);
        $this->assertEquals(['some-permission', 'some-role'], $requiredRolesOrPermissions);
    }

    /** @test */
    public function the_required_permissions_or_roles_can_be_displayed_in_the_exception()
    {
        Auth::login($this->testUser);
        Config::set(['permission.display_permission_in_exception' => true]);
        Config::set(['permission.display_role_in_exception' => true]);

        $message = null;

        try {
            $this->roleOrPermissionMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'some-permission|some-role');
        } catch (UnauthorizedException $e) {
            $message = $e->getMessage();
        }

        $this->assertStringEndsWith('Necessary roles or permissions are some-permission, some-role', $message);
    }

    /** @test */
    public function the_middleware_can_be_created_with_static_using_method()
    {
        $this->assertSame(
            'Spatie\Permission\Middlewares\RoleOrPermissionMiddleware:edit-articles',
            RoleOrPermissionMiddleware::using('edit-articles')
        );
        $this->assertEquals(
            'Spatie\Permission\Middlewares\RoleOrPermissionMiddleware:edit-articles,my-guard',
            RoleOrPermissionMiddleware::using('edit-articles', 'my-guard')
        );
        $this->assertEquals(
            'Spatie\Permission\Middlewares\RoleOrPermissionMiddleware:edit-articles|testAdminRole',
            RoleOrPermissionMiddleware::using(['edit-articles', 'testAdminRole'])
        );
    }
}
