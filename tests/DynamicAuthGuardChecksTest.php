<?php

namespace Spatie\Permission\Test;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Spatie\Permission\Middlewares\RoleOrPermissionMiddleware;
use Spatie\Permission\Models\Permission;

class DynamicAuthGuardChecksTest extends TestCase
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
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.guards', [
            'api' => ['driver' => 'session', 'provider' => 'users'],
            'web' => ['driver' => 'session', 'provider' => 'users'],
            'abc' => ['driver' => 'abc'],
        ]);

        $app['config']->set('permission.enable_dynamic_auth_guard_checks', true);
    }

    /** @test */
    public function it_can_honour_guard_used_for_login()
    {

        $this->testUser->givePermissionTo(Permission::create([
            'name' => 'do_this',
            'guard_name' => 'web',
        ]));

        $this->testUser->givePermissionTo(Permission::create([
            'name' => 'do_that',
            'guard_name' => 'api',
        ]));

        Auth::guard('web')->setUser($this->testUser);

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'do_this'
            ), 200);

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'do_that'
            ), 403);

        Auth::guard('api')->setUser($this->testUser);

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'do_this'
            ), 403);

        $this->assertEquals(
            $this->runMiddleware(
                $this->permissionMiddleware, 'do_that'
            ), 200);
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
