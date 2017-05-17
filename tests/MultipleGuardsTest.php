<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Models\Permission;

class MultipleGuardsTest extends TestCase
{
    /** @test */
    public function it_can_give_a_permission_to_a_model_that_uses_multiple_guards()
    {
        $this->testUser->givePermissionTo(Permission::create([
            'name' => 'cannnn_dooooo',
            'guard_name' => 'web',
        ]));

        $this->testUser->givePermissionTo(Permission::create([
            'name' => 'cannnn_dooooo',
            'guard_name' => 'api',
        ]));
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('auth.guards', [
            'web' => ['driver' => 'session', 'provider' => 'users'],
            'api' => ['driver' => 'jwt', 'provider' => 'users'],
        ]);
    }
}
