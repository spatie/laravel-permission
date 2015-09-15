<?php

namespace Spatie\Permission\Test;

class GateTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_determine_if_a_user_has_a_permission()
    {
        $this->testRole->givePermissionTo($this->testPermission);

        $this->testUser->assignRole($this->testRole);

        $this->assertTrue($this->testUser->hasPermission($this->testPermission));

        $this->assertTrue($this->reloadPermissions());

        $this->assertTrue($this->testUser->can('edit-articles'));

        $this->assertFalse($this->testUser->can('non-existing-permission'));
    }

    /**
     * @test
     */
    public function it_can_determine_if_a_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->can('edit-articles'));
    }
}
