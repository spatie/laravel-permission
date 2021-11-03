<?php

namespace Spatie\Permission\Test;

use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Exceptions\GuardDoesNotMatch;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class HasRolesWithCustomModelsTest extends HasRolesTest
{
    /** @var bool */
    protected $useCustomModels=true;

    /** @test */
    public function it_can_use_custom_model_role()
    {
        $this->assertSame(get_class($this->testUserRole), \Spatie\Permission\Test\Role::class);
    }
}
