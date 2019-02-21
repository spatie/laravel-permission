<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Middleware\Authenticate;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\RoleOrPermissionMiddleware;

class MiddlewareTest extends TestCase
{
    protected $roleMiddleware;
    protected $permissionMiddleware;
    protected $roleOrPermissionMiddleware;

    public function setUp()
    {
        parent::setUp();

        $this->roleMiddleware = new RoleMiddleware($this->app);

        $this->permissionMiddleware = new PermissionMiddleware($this->app);

        $this->roleOrPermissionMiddleware = new RoleOrPermissionMiddleware($this->app);

        $this->authenticateMiddleware = new Authenticate($this->app['auth']);
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

    /** @test */
    public function a_user_can_access_a_route_protected_by_permission_or_role_middleware_if_has_this_permission_or_role()
    {
        Auth::login($this->testUser);

        $this->testUser->assignRole('testRole');
        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-news|edit-articles'),
            200
        );

        $this->testUser->removeRole('testRole');

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles'),
            200
        );

        $this->testUser->revokePermissionTo('edit-articles');
        $this->testUser->assignRole('testRole');

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles'),
            200
        );
    }

    /** @test */
    public function a_user_can_not_access_a_route_protected_by_permission_or_role_middleware_if_have_not_this_permission_and_role()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|edit-articles'),
            403
        );

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'missingRole|missingPermission'),
            403
        );
    }

    /** @test */
    public function the_required_roles_can_be_fetched_from_the_exception()
    {
        Auth::login($this->testUser);

        $requiredRoles = [];

        try {
            $this->roleMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'some-role');
        } catch (UnauthorizedException $e) {
            $requiredRoles = $e->getRequiredRoles();
        }

        $this->assertEquals(['some-role'], $requiredRoles);
    }

    /** @test */
    public function the_required_permissions_can_be_fetched_from_the_exception()
    {
        Auth::login($this->testUser);

        $requiredPermissions = [];

        try {
            $this->permissionMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'some-permission');
        } catch (UnauthorizedException $e) {
            $requiredPermissions = $e->getRequiredPermissions();
        }

        $this->assertEquals(['some-permission'], $requiredPermissions);
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_the_role_middleware_from_a_specific_guard()
    {
        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testAdminRole', 'admin'
            ), 403);
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_the_permission_middleware_from_a_specific_guard()
    {
        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'admin-permission', 'admin'
            ), 403);
    }

    /** @test */
    public function a_guest_can_not_access_a_route_protected_by_permission_or_role_middleware_from_a_specific_guard()
    {
        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testAdminRole|admin-permission', 'admin'),
            403
        );
    }

    /** @test */
    public function an_admin_user_can_access_a_route_protected_by_role_middleware_from_a_specific_guard()
    {
        $this->testAdmin->assignRole('testAdminRole');

        Auth::guard('admin')->login($this->testAdmin);

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testAdminRole', 'admin'
            ), 200);
    }

    /** @test */
    public function an_admin_user_cannot_access_a_route_protected_by_role_middleware_from_a_specific_guard_if_they_do_not_provide_the_guard_name()
    {
        $this->testAdmin->assignRole('testAdminRole');

        Auth::guard('admin')->login($this->testAdmin);

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testAdminRole'
            ), 403);
    }

    /** @test */
    public function an_admin_user_can_access_a_route_protected_by_permission_middleware_from_a_specific_guard()
    {
        $this->testAdmin->givePermissionTo('admin-permission');

        Auth::guard('admin')->login($this->testAdmin);

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'admin-permission', 'admin'
            ), 200);
    }

    /** @test */
    public function an_admin_user_cannot_access_a_route_protected_by_permission_middleware_from_a_specific_guard_if_they_do_not_provide_the_guard_name()
    {
        $this->testAdmin->givePermissionTo('admin-permission');

        Auth::guard('admin')->login($this->testAdmin);

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'admin-permission'
            ), 403);
    }

    /** @test */
    public function an_admin_user_can_access_a_route_protected_by_role_or_permission_middleware_from_a_specific_guard()
    {
        Auth::guard('admin')->login($this->testAdmin);

        $this->testAdmin->assignRole('testAdminRole');
        $this->testAdmin->givePermissionTo('admin-permission');

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testAdminRole|admin-permission', 'admin'),
            200
        );
    }

    /** @test */
    public function an_admin_user_cannot_access_a_route_protected_by_role_or_permission_middleware_from_a_specific_guard_if_they_do_not_provide_the_guard_name()
    {
        Auth::guard('admin')->login($this->testAdmin);

        $this->testAdmin->assignRole('testAdminRole');
        $this->testAdmin->givePermissionTo('admin-permission');

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testAdminRole|admin-permission'),
            403
        );
    }

    /** @test */
    public function logging_in_both_users_and_calling_the_authenticate_middleware_with_a_guard_results_in_a_change_at_runtime_of_the_default_guard_config_value()
    {
        Auth::login($this->testUser);

        $this->runMiddleware(
            $this->authenticateMiddleware
        );

        $this->assertEquals('web', config('auth.defaults.guard'));

        Auth::guard('admin')->login($this->testAdmin);

        $this->runMiddleware(
            $this->authenticateMiddleware, 'admin'
        );

        $this->assertEquals('admin', config('auth.defaults.guard'));
    }

    /** @test */
    public function ensure_that_the_role_middleware_is_not_affected_by_a_change_at_runtime_of_the_default_guard_config_value()
    {
        Auth::login($this->testUser);

        $this->testAdmin->assignRole('testAdminRole');
        Auth::guard('admin')->login($this->testAdmin);

        config(['auth.defaults.guard' => 'admin']);

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testAdminRole'
            ), 403);

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleMiddleware, 'testAdminRole', 'admin'
            ), 200);
    }

    /** @test */
    public function ensure_that_the_permission_middleware_is_not_affected_by_a_change_at_runtime_of_the_default_guard_config_value()
    {
        Auth::login($this->testUser);

        $this->testAdmin->givePermissionTo('admin-permission');
        Auth::guard('admin')->login($this->testAdmin);

        config(['auth.defaults.guard' => 'admin']);

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'admin-permission'
            ), 403);

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'admin-permission', 'admin'
            ), 200);
    }

    /** @test */
    public function ensure_that_the_role_or_permission_middleware_is_not_affected_by_a_change_at_runtime_of_the_default_guard_config_value()
    {
        Auth::login($this->testUser);

        $this->testAdmin->assignRole('testAdminRole');
        $this->testAdmin->givePermissionTo('admin-permission');
        Auth::guard('admin')->login($this->testAdmin);

        config(['auth.defaults.guard' => 'admin']);

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleOrPermissionMiddleware, 'testAdminRole|admin-permission'
            ), 403);

        $this->assertEquals(
            $this->runMiddleware(
                $this->roleOrPermissionMiddleware, 'testAdminRole|admin-permission', 'admin'
            ), 200);
    }

    protected function runMiddleware($middleware, ...$parameters)
    {
        try {
            return $middleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, ...$parameters)->status();
        } catch (UnauthorizedException $e) {
            return $e->getStatusCode();
        }
    }
}
