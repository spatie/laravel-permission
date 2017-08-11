<?php

namespace Spatie\Permission\Test;

class PrefixedModelTest extends TestCase
{
    /** @test */
    public function check_successful_permission_on_model_with_prefixed_table()
    {
        $this->testPrefixedUser->givePermissionTo($this->testPrefixedUserPermission);

        $this->refreshTestPrefixedUser();

        $this->assertTrue($this->testPrefixedUser->hasPermissionTo($this->testPrefixedUserPermission));
    }

    /** @test */
    public function check_successful_role_on_model_with_prefixed_table()
    {
        $this->testPrefixedUser->assignRole($this->testPrefixedUserRole);
        $this->assertTrue($this->testPrefixedUser->hasRole($this->testPrefixedUserRole));
    }
}
