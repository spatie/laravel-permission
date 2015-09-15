<?php

namespace Spatie\Permission\Test;

class HasRolesTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_determine_that_the_user_does_not_have_a_role()
    {
        $this->assertFalse($this->testUser->hasRole('testRole'));
    }

    /**
     * @test
     */
    public function it_can_assign_a_role()
    {
        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasRole('testRole'));
    }

    /**
     * @test
     */
    public function it_can_assign_a_role_using_an_object()
    {
        $this->testUser->assignRole($this->testRole);

        $this->testUser = User::find($this->testUser->id);

        $this->assertTrue($this->testUser->hasRole($this->testRole));
    }

    /**
     * @test
     */
    public function it_can_determine_if_a_user_has_multiple_roles()
    {
        $this->testUser->assignRole($this->testRole);

        $this->assertTrue($this->testUser->hasRole($this->testUser->roles));
    }

    /**
     * @test
     */
    public function it_can_determine_that_the_user_does_not_have_a_permission()
    {
        $this->assertFalse($this->testUser->hasPermission('edit-articles'));
    }

    /**
     * @test
     */
    public function it_can_assign_a_permission_to_a_role()
    {
        $this->testRole->givePermissionTo('edit-articles');

        $this->testUser->assignRole('testRole');

        $this->assertTrue($this->testUser->hasPermission('edit-articles'));
    }

    /**
     * @test
     */
    public function it_can_assign_a_permission_to_a_role_using_objects()
    {
        $this->testRole->givePermissionTo($this->testPermission);

        $this->testUser->assignRole($this->testRole);

        $this->assertTrue($this->testUser->hasPermission($this->testPermission));
    }
}
