<?php

namespace Spatie\Permission\Tests;

use Spatie\Permission\Tests\TestModels\UserHasBlockedPermission;

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
}
