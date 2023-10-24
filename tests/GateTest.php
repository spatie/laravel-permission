<?php

namespace Spatie\Permission\Tests;

use Illuminate\Contracts\Auth\Access\Gate;
use Spatie\Permission\Contracts\Permission;

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
            // this Gate-before intercept overrides everything to true ... like a typical Super-Admin might use
            return true;
        });

        $this->assertTrue($this->testUser->can('edit-articles'));
    }

    /** @test */
    public function it_allows_gate_after_callback_to_grant_denied_privileges()
    {
        $this->assertFalse($this->testUser->can('edit-articles'));

        app(Gate::class)->after(function ($user, $ability, $result) {
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

    /**
     * @test
     *
     * @requires PHP >= 8.1
     */
    public function it_can_determine_if_a_user_has_a_direct_permission_using_enums()
    {
        $enum = TestModels\TestRolePermissionsEnum::VIEWARTICLES;

        $permission = app(Permission::class)->findOrCreate($enum->value, 'web');

        $this->assertFalse($this->testUser->can($enum->value));
        $this->assertFalse($this->testUser->canAny([$enum->value, 'some other permission']));

        $this->testUser->givePermissionTo($enum);

        $this->assertTrue($this->testUser->hasPermissionTo($enum));

        $this->assertTrue($this->testUser->can($enum->value));
        $this->assertTrue($this->testUser->canAny([$enum->value, 'some other permission']));
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
