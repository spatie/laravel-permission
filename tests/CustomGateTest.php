<?php

namespace Spatie\Permission\Tests;

use Illuminate\Contracts\Auth\Access\Gate;
use PHPUnit\Framework\Attributes\Test;

class CustomGateTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('permission.register_permission_check_method', false);
    }

    /** @test */
    #[Test]
    public function it_doesnt_register_the_method_for_checking_permissions_on_the_gate()
    {
        $this->testUser->givePermissionTo('edit-articles');

        $this->assertEmpty(app(Gate::class)->abilities());
        $this->assertFalse($this->testUser->can('edit-articles'));
    }

    /** @test */
    #[Test]
    public function it_can_authorize_using_custom_method_for_checking_permissions()
    {
        app(Gate::class)->define('edit-articles', function () {
            return true;
        });

        $this->assertArrayHasKey('edit-articles', app(Gate::class)->abilities());
        $this->assertTrue($this->testUser->can('edit-articles'));
    }
}
