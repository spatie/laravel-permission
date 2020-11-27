<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\PermissionMiddleware;

class PermissionMiddlewareTest extends TestCase
{
    protected $permissionMiddleware;

    public function setUp(): void
    {
        parent::setUp();

        $this->permissionMiddleware = new PermissionMiddleware();
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_the_permission_middleware()
    {
        $this->assertEquals(403,
            $this->runMiddleware($this->permissionMiddleware, 'edit-articles')
        );
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_the_permission_middleware_of_a_different_guard()
    {
        // These permissions are created fresh here in reverse order of guard being applied, so they are not "found first" in the db lookup when matching
        app(Permission::class)->create(['name' => 'admin-permission2', 'guard_name' => 'web']);
        app(Permission::class)->create(['name' => 'admin-permission2', 'guard_name' => 'admin']);
        app(Permission::class)->create(['name' => 'edit-articles2', 'guard_name' => 'admin']);
        app(Permission::class)->create(['name' => 'edit-articles2', 'guard_name' => 'web']);

        Auth::login($this->testAdmin);

        $this->testAdmin->givePermissionTo('admin-permission2');

        $this->assertEquals(200,
            $this->runMiddleware($this->permissionMiddleware, 'admin-permission2')
        );

        $this->assertEquals(403,
            $this->runMiddleware($this->permissionMiddleware, 'edit-articles2')
        );

        Auth::login($this->testUser);

        $this->testUser->givePermissionTo('edit-articles2');

        $this->assertEquals(200,
            $this->runMiddleware($this->permissionMiddleware, 'edit-articles2')
        );

        $this->assertEquals(403,
            $this->runMiddleware($this->permissionMiddleware, 'admin-permission2')
        );
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_permission_middleware_if_have_this_permission()
    {
        Auth::login($this->testUser);

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(200,
            $this->runMiddleware($this->permissionMiddleware, 'edit-articles')
        );
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_this_permission_middleware_if_have_one_of_the_permissions()
    {
        Auth::login($this->testUser);

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(200,
            $this->runMiddleware($this->permissionMiddleware, 'edit-news|edit-articles')
        );

        $this->assertEquals(200,
            $this->runMiddleware($this->permissionMiddleware, ['edit-news', 'edit-articles'])
        );
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_the_permission_middleware_if_have_a_different_permission()
    {
        Auth::login($this->testUser);

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(403,
            $this->runMiddleware($this->permissionMiddleware, 'edit-news')
        );
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_permission_middleware_if_have_not_permissions()
    {
        Auth::login($this->testUser);

        $this->assertEquals(403,
            $this->runMiddleware($this->permissionMiddleware, 'edit-articles|edit-news')
        );
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
    public function use_not_existing_custom_guard_in_permission()
    {
        $class = null;

        try {
            $this->permissionMiddleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, 'edit-articles', 'xxx');
        } catch (InvalidArgumentException $e) {
            $class = get_class($e);
        }

        $this->assertEquals(InvalidArgumentException::class, $class);
    }

    /** @test */
    public function user_can_not_access_permission_with_guard_admin_while_using_default_guard()
    {
        Auth::login($this->testUser);

        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEquals(403,
            $this->runMiddleware($this->permissionMiddleware, 'edit-articles', 'admin')
        );
    }

    /** @test */
    public function user_can_access_permission_with_guard_admin_while_using_default_guard()
    {
        Auth::guard('admin')->login($this->testAdmin);

        $this->testAdmin->givePermissionTo('admin-permission');

        $this->assertEquals(200,
            $this->runMiddleware($this->permissionMiddleware, 'admin-permission', 'admin')
        );
    }

    protected function runMiddleware($middleware, $permission, $guard = null)
    {
        try {
            return $middleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, $permission, $guard)->status();
        } catch (UnauthorizedException $e) {
            return $e->getStatusCode();
        }
    }
}
