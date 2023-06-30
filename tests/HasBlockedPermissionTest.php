<?php

namespace Spatie\Permission\Tests;

use Spatie\Permission\Tests\TestModels\User;

class HasBlockedPermissionTest extends TestCase
{
    /**
     * @test
     */
    public function it_block_user_from_permission()
    {
        $this->testUser->blockFromPermission($this->testUserPermission);

        $this->assertTrue($this->testUser->hasBlockFromPermission($this->testUserPermission));
    }

    /**
     * @test
     */
    public function it_return_false_when_user_not_block_from_permission()
    {
        $this->assertFalse($this->testUser->hasBlockFromPermission($this->testUserPermission));
    }

    /**
     * @test
     */
    public function it_check_multiple_permission_is_blocked()
    {
        $this->testUser->blockFromPermission(['edit-articles', 'edit-news']);

        $this->assertTrue($this->testUser->hasBlockFromAnyPermission(['edit-articles', 'edit-news']));
    }

    /**
     * @test
     */
    public function it_check_any_permissions_are_blocked()
    {
        $this->testUser->blockFromPermission('edit-articles');

        $this->assertTrue($this->testUser->hasBlockFromAnyPermission(['edit-articles', 'edit-news']));
    }
}
