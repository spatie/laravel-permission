<?php

namespace Spatie\Permission\Test;

use DB;

class HasRolesWithCustomModelsTest extends HasRolesTest
{
    /** @var bool */
    protected $useCustomModels = true;

    /** @test */
    public function it_can_use_custom_model_role()
    {
        $this->assertSame(get_class($this->testUserRole), Role::class);
    }

    /** @test */
    public function it_doesnt_detach_permissions_when_soft_deleting()
    {
        $this->testUserRole->givePermissionTo($this->testUserPermission);

        DB::enableQueryLog();
        $this->testUserRole->delete();
        DB::disableQueryLog();

        $this->assertSame(1, count(DB::getQueryLog()));

        $role = Role::onlyTrashed()->find($this->testUserRole->getKey());

        $this->assertEquals(1, DB::table(config('permission.table_names.role_has_permissions'))->where('role_test_id', $role->getKey())->count());
    }

    /** @test */
    public function it_doesnt_detach_users_when_soft_deleting()
    {
        $this->testUser->assignRole($this->testUserRole);

        DB::enableQueryLog();
        $this->testUserRole->delete();
        DB::disableQueryLog();

        $this->assertSame(1, count(DB::getQueryLog()));

        $role = Role::onlyTrashed()->find($this->testUserRole->getKey());

        $this->assertEquals(1, DB::table(config('permission.table_names.model_has_roles'))->where('role_test_id', $role->getKey())->count());
    }

    /** @test */
    public function it_does_detach_permissions_and_users_when_force_deleting()
    {
        $role_id = $this->testUserRole->getKey();
        $this->testUserPermission->assignRole($role_id);
        $this->testUser->assignRole($role_id);

        DB::enableQueryLog();
        $this->testUserRole->forceDelete();
        DB::disableQueryLog();

        $this->assertSame(3, count(DB::getQueryLog()));

        $role = Role::withTrashed()->find($role_id);

        $this->assertNull($role);
        $this->assertEquals(0, DB::table(config('permission.table_names.role_has_permissions'))->where('role_test_id', $role_id)->count());
        $this->assertEquals(0, DB::table(config('permission.table_names.model_has_roles'))->where('role_test_id', $role_id)->count());
    }
}
