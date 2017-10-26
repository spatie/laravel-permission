<?php

namespace Spatie\Permission\Test;

use Illuminate\Contracts\Auth\Access\Gate;

class GateTest extends TestCase
{
    /** @test */
    public function it_can_determine_if_a_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->can('edit-articles'));
    }

    /** @test */
    public function it_allows_other_gate_before_callbacks_to_run_if_a_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->can('edit-articles'));

        app(Gate::class)->before(function () {
            return true;
        });

        $this->assertTrue($this->testUser->can('edit-articles'));
    }

    /** @test */
    public function it_can_determine_if_a_user_has_a_direct_permission()
    {
        $this->testUser->givePermissionTo('edit-articles');

        $this->assertTrue($this->testUser->can('edit-articles'));

        $this->assertFalse($this->testUser->can('non-existing-permission'));

        $this->assertFalse($this->testUser->can('admin-permission'));
    }

    /** @test */
    public function it_can_determine_if_a_user_has_a_permission_through_roles()
    {
        $this->testUserRole->givePermissionTo($this->testUserPermission);

        $this->testUser->assignRole($this->testUserRole);

        $this->assertTrue($this->testUser->hasPermissionTo($this->testUserPermission));

        $this->assertTrue($this->testUser->can('edit-articles'));

        $this->assertFalse($this->testUser->can('non-existing-permission'));

        $this->assertFalse($this->testUser->can('admin-permission'));
    }

    /** @test */
    public function it_can_determine_if_a_user_with_a_different_guard_has_a_permission_when_using_roles()
    {
        $this->testAdminRole->givePermissionTo($this->testAdminPermission);

        $this->testAdmin->assignRole($this->testAdminRole);

        $this->assertTrue($this->testAdmin->hasPermissionTo($this->testAdminPermission));

        $this->assertTrue($this->testAdmin->can('admin-permission'));

        $this->assertFalse($this->testAdmin->can('non-existing-permission'));

        $this->assertFalse($this->testAdmin->can('edit-articles'));
    }
}
