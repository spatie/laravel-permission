<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class HasPermissionsTest extends TestCase
{
    /** @test */
    public function it_can_determine_that_a_user_has_one_of_the_given_permissions_by_string()
    {
        $permissionModel = app(Permission::class);

        $permissionModel->create(['name' => $permission_name = 'first-permission']);

        $this->testUser->givePermissionTo($permission_name);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAnyPermission($permission_name));
    }

    /** @test */
    public function it_can_determine_that_a_user_has_one_of_the_given_permissions_by_permission_class()
    {
        $permissionModel = app(Permission::class);

        $permission = $permissionModel->create(['name' => 'first-permission']);

        $this->assertFalse($this->testUser->hasAnyPermission($permission));

        $this->testUser->givePermissionTo($permission);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAnyPermission($permission));
    }

    /** @test */
    public function it_can_determine_that_a_user_has_one_of_the_given_permissions_by_array_of_permission_classes()
    {
        $permissionModel = app(Permission::class);

        $permissions = [
            $permissionModel->create(['name' => 'first-permission']),
            $permissionModel->create(['name' => 'second-permission']),
        ];

        $this->assertFalse($this->testUser->hasAnyPermission($permissions));

        $this->testUser->givePermissionTo('first-permission');

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAnyPermission($permissions));
    }

    /** @test */
    public function it_can_determine_that_a_user_has_one_of_the_given_permissions_by_array_of_strings()
    {
        $permissionModel = app(Permission::class);

        $permissionModel->create(['name' => $first_permission = 'first-permission']);
        $permissionModel->create(['name' => $second_permission = 'second-permission']);

        $permissions = [
            $first_permission,
            $second_permission,
        ];

        $this->assertFalse($this->testUser->hasAnyPermission($permissions));

        $this->testUser->givePermissionTo($first_permission);

        $this->refreshTestUser();

        $this->assertTrue($this->testUser->hasAnyPermission($permissions));
    }
}
