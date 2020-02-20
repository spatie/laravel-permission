<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\RoleOrPermissionMiddleware;

class WildcardMiddlewareTest extends TestCase
{
    protected $roleMiddleware;
    protected $permissionMiddleware;
    protected $roleOrPermissionMiddleware;

    public function setUp(): void
    {
        parent::setUp();

        $this->roleMiddleware = new RoleMiddleware();

        $this->permissionMiddleware = new PermissionMiddleware();

        $this->roleOrPermissionMiddleware = new RoleOrPermissionMiddleware();

        app('config')->set('permission.enable_wildcard_permission', true);
    }

    /** @test */
    public function a_guest_cannot_access_a_route_protected_by_the_permission_middleware()
    {
        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'articles.edit'
            ), 403);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_permission_middleware_if_have_this_permission()
    {
        Auth::login($this->testUser);

        Permission::create(['name' => 'articles']);

        $this->testUser->givePermissionTo('articles');

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'articles.edit'
            ), 200);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_this_permission_middleware_if_have_one_of_the_permissions()
    {
        Auth::login($this->testUser);

        Permission::create(['name' => 'articles.*.test']);

        $this->testUser->givePermissionTo('articles.*.test');

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'news.edit|articles.create.test'
            ), 200);

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, ['news.edit', 'articles.create.test']
            ), 200);
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_the_permission_middleware_if_have_a_different_permission()
    {
        Auth::login($this->testUser);

        Permission::create(['name' => 'articles.*']);

        $this->testUser->givePermissionTo('articles.*');

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'news.edit'
            ), 403);
    }

    /** @test */
    public function a_user_cannot_access_a_route_protected_by_permission_middleware_if_have_not_permissions()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'articles.edit|news.edit'
            ), 403);
    }

    /** @test */
    public function a_user_can_access_a_route_protected_by_permission_or_role_middleware_if_has_this_permission_or_role()
    {
        Auth::login($this->testUser);

        Permission::create(['name' => 'articles.*']);

        $this->testUser->assignRole('testRole');
        $this->testUser->givePermissionTo('articles.*');

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|news.edit|articles.create'),
            200
        );

        $this->testUser->removeRole('testRole');

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|articles.edit'),
            200
        );

        $this->testUser->revokePermissionTo('articles.*');
        $this->testUser->assignRole('testRole');

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, 'testRole|articles.edit'),
            200
        );

        $this->assertEquals(
            $this->runMiddleware($this->roleOrPermissionMiddleware, ['testRole', 'articles.edit']),
            200
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
            }, 'permission.some');
        } catch (UnauthorizedException $e) {
            $requiredPermissions = $e->getRequiredPermissions();
        }

        $this->assertEquals(['permission.some'], $requiredPermissions);
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
