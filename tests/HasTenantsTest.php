<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Contracts\Tenant;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class HasTenantsTest extends TestCase
{
    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        parent::setUpDatabase($app);

        $app[Tenant::class]->create(['tenant_name' => 'testTenant']);
        $app[Tenant::class]->create(['tenant_name' => 'testTenant2']);
    }

    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_role_with_the_tenant_id()
    {
        $this->assertFalse($this->testUser->hasPermissionToTenant('edit-articles', 1));
    }

    /** @test */
    public function it_can_determine_that_the_user_does_not_have_a_role_with_the_tenant_class()
    {
        $this->assertInstanceOf('\Spatie\Permission\Contracts\Tenant', $this->testUserTenant);
        $this->assertFalse($this->testUser->hasPermissionToTenant('edit-articles', $this->testUserTenant));
    }

    /** @test */
    public function it_can_determine_that_having_access_to_one_tenant_does_not_grant_access_to_another()
    {
        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->assignRoleToTenant($this->testUserRole->id, 1);
        $this->assertTrue($this->testUser->hasPermissionToTenant('edit-articles', 1));
        $this->assertFalse($this->testUser->hasPermissionToTenant('edit-articles', 2));
    }

    /** @test */
    public function it_can_determine_that_having_one_privilege_to_a_tenant_does_not_grant_another_privilege()
    {
        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->assignRoleToTenant($this->testUserRole->id, 1);
        $this->assertTrue($this->testUser->hasPermissionToTenant('edit-articles', 1));
        $this->assertFalse($this->testUser->hasPermissionToTenant('edit-news', 1));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role_with_a_role_id_and_a_tenant_id()
    {
        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->assignRoleToTenant($this->testUserRole->id, 1);
        $this->assertTrue($this->testUser->hasPermissionToTenant('edit-articles', 1));

        $this->testUser->removeRoleFromTenant($this->testUserRole->id, 1);
        $this->refreshTestUser();
        $this->assertFalse($this->testUser->hasPermissionToTenant('edit-articles', 1));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role_with_a_permission_name_and_a_tenant_id()
    {
        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->assignRoleToTenant($this->testUserRole->name, 1);
        $this->assertTrue($this->testUser->hasPermissionToTenant('edit-articles', 1));

        $this->testUser->removeRoleFromTenant('testRole', 1);
        $this->refreshTestUser();
        $this->assertFalse($this->testUser->hasPermissionToTenant('edit-articles', 1));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role_with_a_role_id_array_and_a_tenant_id()
    {
        $testRole1 = $this->testUserRole->find(1);
        $testRole2 = $this->testUserRole->find(2);
        $testRole1->givePermissionTo('edit-articles');
        $testRole2->givePermissionTo('edit-news');
        $this->testUser->assignRoleToTenant([$testRole1->id, $testRole2->id], $this->testUserTenant);
        $this->assertTrue($this->testUser->hasPermissionToTenant('edit-articles', $this->testUserTenant));
        $this->assertTrue($this->testUser->hasPermissionToTenant('edit-news', $this->testUserTenant));

        $this->testUser->removeRoleFromTenant([$testRole1->id, $testRole2->id], $this->testUserTenant);
        $this->refreshTestUser();
        $this->assertFalse($this->testUser->hasPermissionToTenant('edit-articles', $this->testUserTenant));
        $this->assertFalse($this->testUser->hasPermissionToTenant('edit-news', $this->testUserTenant));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role_with_a_permission_object_and_a_tenant_id()
    {
        $this->testUserRole->givePermissionTo($this->testUserPermission->name);
        $this->testUser->assignRoleToTenant($this->testUserRole->name, $this->testUserTenant);
        $this->assertTrue($this->testUser->hasPermissionToTenant($this->testUserPermission, $this->testUserTenant));

        $this->testUser->removeRoleFromTenant($this->testUserRole->name, $this->testUserTenant);
        $this->refreshTestUser();
        $this->assertFalse($this->testUser->hasPermissionToTenant($this->testUserPermission, $this->testUserTenant));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role_with_a_role_id_and_a_tenant_object()
    {
        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->assignRoleToTenant($this->testUserRole->id, $this->testUserTenant);
        $this->assertTrue($this->testUser->hasPermissionToTenant('edit-articles', $this->testUserTenant));

        $this->testUser->removeRoleFromTenant($this->testUserRole->id, $this->testUserTenant);
        $this->refreshTestUser();
        $this->assertFalse($this->testUser->hasPermissionToTenant('edit-articles', $this->testUserTenant));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role_with_a_permission_name_and_a_tenant_object()
    {
        $this->testUserRole->givePermissionTo('edit-articles');
        $this->testUser->assignRoleToTenant($this->testUserRole->name, $this->testUserTenant);
        $this->assertTrue($this->testUser->hasPermissionToTenant('edit-articles', $this->testUserTenant));

        $this->testUser->removeRoleFromTenant('testRole', $this->testUserTenant);
        $this->refreshTestUser();
        $this->assertFalse($this->testUser->hasPermissionToTenant('edit-articles', $this->testUserTenant));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role_with_a_role_id_array_and_a_tenant_object()
    {
        $testRole1 = $this->testUserRole->find(1);
        $testRole2 = $this->testUserRole->find(2);
        $testRole1->givePermissionTo('edit-articles');
        $testRole2->givePermissionTo('edit-news');
        $this->testUser->assignRoleToTenant([$testRole1->id, $testRole2->id], $this->testUserTenant);
        $this->assertTrue($this->testUser->hasPermissionToTenant('edit-articles', $this->testUserTenant));
        $this->assertTrue($this->testUser->hasPermissionToTenant('edit-news', $this->testUserTenant));

        $this->testUser->removeRoleFromTenant([$testRole1->id, $testRole2->id], $this->testUserTenant);
        $this->refreshTestUser();
        $this->assertFalse($this->testUser->hasPermissionToTenant('edit-articles', $this->testUserTenant));
        $this->assertFalse($this->testUser->hasPermissionToTenant('edit-news', $this->testUserTenant));
    }

    /** @test */
    public function it_can_assign_and_remove_a_role_with_a_permission_object_and_a_tenant_object()
    {
        $this->testUserRole->givePermissionTo($this->testUserPermission->name);
        $this->testUser->assignRoleToTenant($this->testUserRole->name, $this->testUserTenant);
        $this->assertTrue($this->testUser->hasPermissionToTenant($this->testUserPermission, $this->testUserTenant));

        $this->testUser->removeRoleFromTenant($this->testUserRole->name, $this->testUserTenant);
        $this->refreshTestUser();
        $this->assertFalse($this->testUser->hasPermissionToTenant($this->testUserPermission, $this->testUserTenant));
    }

    /** @test */
    public function it_throws_an_exception_when_assigning_a_role_that_does_not_exist()
    {
        $this->expectException(RoleDoesNotExist::class);

        $this->testUser->assignRole('evil-emperor', 1);
    }
}
