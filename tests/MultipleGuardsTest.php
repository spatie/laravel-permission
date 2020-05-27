<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Models\Permission;

class MultipleGuardsTest extends TestCase
{
    /** @test */
    public function it_can_give_a_permission_to_a_model_that_is_used_by_multiple_guards()
    {
        $this->testUser->givePermissionTo(Permission::create([
            'name' => 'do_this',
            'guard_name' => 'web',
        ]));

        $this->testUser->givePermissionTo(Permission::create([
            'name' => 'do_that',
            'guard_name' => 'api',
        ]));

        $this->assertTrue($this->testUser->hasPermissionTo('do_this', 'web'));
        $this->assertTrue($this->testUser->hasPermissionTo('do_that', 'api'));
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.guards', [
            'web' => ['driver' => 'session', 'provider' => 'users'],
            'api' => ['driver' => 'jwt', 'provider' => 'users'],
            'abc' => ['driver' => 'abc'],
        ]);
    }

    /** @test */
    public function it_can_honour_guardName_function_on_model_for_overriding_guard_name_property()
    {
        $user = Manager::create(['email' => 'manager@test.com']);
        $user->givePermissionTo(Permission::create([
            'name' => 'do_that',
            'guard_name' => 'api',
        ]));

        // Manager test user has the guardName override method, which returns 'api'
        $this->assertTrue($user->checkPermissionTo('do_that', 'api'));
        $this->assertTrue($user->hasPermissionTo('do_that', 'api'));

        // Manager test user has the $guard_name property set to 'web'
        $this->assertFalse($user->checkPermissionTo('do_that', 'web'));
    }
}
