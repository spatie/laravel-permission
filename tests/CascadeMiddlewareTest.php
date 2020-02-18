<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\CascadePermissionMiddleware;

class CascadeMiddlewareTest extends TestCase
{
    protected $cascadeMiddleware;

    public function setUp(): void
    {
        parent::setUp();

        app(Permission::class)->create(['name' => 'admin', 'guard_name' => 'admin']);
        app(Permission::class)->create(['name' => 'admin.auth', 'guard_name' => 'admin']);
        app(Permission::class)->create(['name' => 'admin.auth.users', 'guard_name' => 'admin']);
        app(Permission::class)->create(['name' => 'admin.auth.users.modify', 'guard_name' => 'admin']);

        $this->cascadeMiddleware = new CascadePermissionMiddleware($this->app);
    }

    /** @test */
    public function guests_cannot_access_a_route_protected_by_cascade_middleware()
    {
        $this->assertEquals(
            $this->runMiddleware($this->cascadeMiddleware, 'a.permission.assigned.to.the.route'),
            Response::HTTP_FORBIDDEN
        );
    }

    /** @test */
    public function logged_in_user_can_pass_cascademiddleware_if_one_of_the_cascade_permissions_match()
    {
        // This user is authenticated by the `admin` guard, which matches the guard_name of permissions created in setUp()
        Auth::login($this->testAdmin);

        // 'admin' here is part of the permission 'name', and has no relation to the 'guard_name'
        Auth::user()->givePermissionTo('admin.auth');

        $this->assertEquals(
            $this->runMiddleware($this->cascadeMiddleware, 'admin.auth.users'),
            Response::HTTP_OK
        );
    }

    /** @test */
    public function logged_in_user_cannot_pass_cascademiddleware_if_not_enough_of_the_cascade_permissions_match()
    {
        Auth::login($this->testAdmin);

        Auth::user()->givePermissionTo('admin.auth.users');

        $this->assertEquals(
            $this->runMiddleware($this->cascadeMiddleware, 'admin.auth'),
            Response::HTTP_FORBIDDEN
        );
    }

    /** @test */
    public function logged_in_user_cannot_pass_cascademiddleware_if_no_cascade_permissions_match()
    {
        Auth::login($this->testUser);

        $this->assertEquals(
            $this->runMiddleware($this->cascadeMiddleware, 'something.not.assigned'),
            Response::HTTP_FORBIDDEN
        );
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
