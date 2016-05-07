<?php namespace Spatie\Permission\Test;

class RoleTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->testRole->givePermissionTo($this->testPermission);
    }

    /** @test */
    public function it_returns_true_if_role_has_permission()
    {
        $this->assertTrue($this->testRole->hasPermissionTo('edit-articles'));
    }

    /** @test */
    public function it_returns_false_if_role_has_not_permission()
    {
        $this->assertFalse($this->testRole->hasPermissionTo('some-fake-permission'));
    }
}

