<?php

namespace Spatie\Permission\Test;

class HasPermissionsWithCustomModelsTest extends HasPermissionsTest
{
    /** @var bool */
    protected $useCustomModels = true;

    /** @test */
    public function it_can_use_custom_models()
    {
        $this->assertSame(get_class($this->testUserPermission), \Spatie\Permission\Test\Permission::class);
        $this->assertSame(get_class($this->testUserRole), \Spatie\Permission\Test\Role::class);
    }
}
